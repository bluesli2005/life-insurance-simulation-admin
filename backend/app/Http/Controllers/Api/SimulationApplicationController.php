<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimulationApplicationIndexRequest;
use App\Http\Requests\SimulationApplicationRequest;
use App\Http\Resources\SimulationApplicationResource;
use App\Models\SimulationApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** 保険申込の検索・登録・更新・削除と監査履歴を扱う。 */
class SimulationApplicationController extends Controller
{
    /** whereRaw の列名として利用できる検索対象を固定する。 */
    const SEARCHABLE_COLUMNS = [
        'application_number',
        'applicant_name',
        'insured_name',
        'beneficiary_name',
        'notes',
    ];

    public function index(SimulationApplicationIndexRequest $request)
    {
        $filters = $request->validated();
        $query = SimulationApplication::query();

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            // LIKE のワイルドカードをエスケープし、利用者入力を文字列として検索する。
            $term = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
            // 複数項目の OR 条件をまとめ、ステータス条件とは AND で組み合わせる。
            $query->where(function ($query) use ($term) {
                foreach (self::SEARCHABLE_COLUMNS as $column) {
                    // 列名は固定リスト内のみ。検索値は常にバインドする。
                    $query->orWhereRaw($column." LIKE ? ESCAPE '!'", ['%'.$term.'%']);
                }
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 作成日時が同じ場合もページ間で並び順が変わらないよう、ID を併用する。
        $applications = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 10);
        $applications->appends($filters);

        return response()->json([
            'data' => SimulationApplicationResource::collection($applications->getCollection())->resolve(),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'from' => $applications->firstItem(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'to' => $applications->lastItem(),
                'total' => $applications->total(),
            ],
            'links' => [
                'first' => $applications->url(1),
                'last' => $applications->url($applications->lastPage()),
                'prev' => $applications->previousPageUrl(),
                'next' => $applications->nextPageUrl(),
            ],
        ]);
    }

    public function store(SimulationApplicationRequest $request)
    {
        $attributes = $request->validated();
        $attributes['currency'] = $attributes['currency'] ?? 'JPY';
        // 申込と監査履歴を同じトランザクションで保存する。
        $application = DB::transaction(function () use ($request, $attributes) {
            $application = SimulationApplication::create($attributes);
            $this->recordChange($application, $request->user()->id, 'created', null, $application->getAttributes());

            return $application;
        });

        return response()->json([
            'data' => (new SimulationApplicationResource($application))->resolve(),
        ], 201);
    }

    public function show(SimulationApplication $simulation_application)
    {
        return response()->json([
            'data' => (new SimulationApplicationResource($simulation_application))->resolve(),
        ]);
    }

    public function update(SimulationApplicationRequest $request, SimulationApplication $simulation_application)
    {
        DB::transaction(function () use ($request, $simulation_application) {
            $before = $simulation_application->getAttributes();
            $simulation_application->fill($request->validated());
            // 値が変わらない更新では監査履歴を増やさない。
            if ($simulation_application->isDirty()) {
                $simulation_application->save();
                $this->recordChange($simulation_application, $request->user()->id, 'updated', $before, $simulation_application->getAttributes());
            }
        });

        return response()->json([
            'data' => (new SimulationApplicationResource($simulation_application->fresh()))->resolve(),
        ]);
    }

    public function destroy(Request $request, SimulationApplication $simulation_application)
    {
        DB::transaction(function () use ($request, $simulation_application) {
            $before = $simulation_application->getAttributes();
            // 論理削除後も変更前後の状態を監査履歴に残す。
            $simulation_application->delete();
            $this->recordChange($simulation_application, $request->user()->id, 'deleted', $before, $simulation_application->getAttributes());
        });

        return response()->json(['data' => null]);
    }

    public function history($id)
    {
        // 削除済みの申込も監査履歴を参照できる。
        SimulationApplication::withTrashed()->findOrFail($id);
        $events = DB::table('simulation_application_audits')->where('application_id', $id)
            ->orderBy('id')->get()->map(function ($event) {
                return [
                    'id' => $event->id,
                    'actor_user_id' => $event->actor_user_id,
                    'action' => $event->action,
                    'before' => $event->before_data ? json_decode($event->before_data, true) : null,
                    'after' => $event->after_data ? json_decode($event->after_data, true) : null,
                    'created_at' => $event->created_at,
                ];
            });

        return response()->json(['data' => $events]);
    }

    private function recordChange(SimulationApplication $application, $actorId, $action, $before, $after)
    {
        DB::table('simulation_application_audits')->insert([
            'application_id' => $application->id,
            'actor_user_id' => $actorId,
            'action' => $action,
            'before_data' => $before === null ? null : json_encode($before, JSON_UNESCAPED_UNICODE),
            'after_data' => $after === null ? null : json_encode($after, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
    }
}
