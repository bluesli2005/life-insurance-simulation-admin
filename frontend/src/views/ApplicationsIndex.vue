<template>
    <section class="page-section">
        <div class="page-heading">
            <div>
                <h2>申込一覧</h2>
                <p class="muted">保険シミュレーションの申込を検索・管理します。</p>
            </div>
            <router-link v-if="canEdit" class="button button-primary" to="/admin/applications/create">申込登録</router-link>
        </div>

        <form class="filter-panel" @submit.prevent="search">
            <label class="field filter-keyword">
                <span>キーワード</span>
                <input v-model="searchText" type="search" maxlength="100" placeholder="申込番号、氏名、備考">
            </label>
            <BaseSelect v-model="statusFilter" name="status-filter" label="ステータス" :options="statusOptions" placeholder="すべて" />
            <div class="filter-actions">
                <BaseButton type="submit">検索</BaseButton>
                <BaseButton variant="secondary" @click="reset">リセット</BaseButton>
            </div>
        </form>

        <ContentState :loading="loading" :error="error" :empty="!applications.length" empty-message="申込データがありません。" retry @retry="loadCurrentPage">
            <BaseTable :columns="columns" :rows="tableRows">
                <template v-slot:actions="{ row }">
                    <router-link class="table-link" :to="`/admin/applications/${row.id}`">詳細</router-link>
                </template>
            </BaseTable>
        </ContentState>

        <div v-if="!loading && !error && total" class="pagination-bar">
            <label class="page-size-control">
                <span>表示件数</span>
                <select :value="perPage" @change="changePageSize($event.target.value)">
                    <option v-for="size in pageSizes" :key="size" :value="size">{{ size }}件</option>
                </select>
            </label>
            <span class="pagination-summary">{{ total }} 件中 {{ from }}–{{ to }} 件</span>
            <div class="pagination-actions">
                <BaseButton variant="secondary" :disabled="currentPage <= 1" @click="goToPage(currentPage - 1)">前へ</BaseButton>
                <span>{{ currentPage }} / {{ lastPage }}</span>
                <BaseButton variant="secondary" :disabled="currentPage >= lastPage" @click="goToPage(currentPage + 1)">次へ</BaseButton>
            </div>
        </div>
    </section>
</template>

<script>
import { mapState } from 'vuex';
import BaseButton from '../components/BaseButton.vue';
import BaseSelect from '../components/BaseSelect.vue';
import BaseTable from '../components/BaseTable.vue';
import ContentState from '../components/ContentState.vue';

const statusLabels = {
    draft: '下書き',
    submitted: '申込済み',
    approved: '承認済み',
    rejected: '却下',
    cancelled: '取消',
};

export default {
    components: { BaseButton, BaseSelect, BaseTable, ContentState },
    data() {
        return {
            searchText: this.$store.state.filters.search,
            statusFilter: this.$store.state.filters.status,
            pageSizes: [10, 20, 30, 50],
            statusOptions: Object.keys(statusLabels).map(value => ({ value, label: statusLabels[value] })),
            columns: [
                { key: 'application_number', label: '申込番号' },
                { key: 'applicant_name', label: '申込者氏名' },
                { key: 'insured_name', label: '被保険者氏名' },
                { key: 'coverage', label: '保険金額' },
                { key: 'status_label', label: 'ステータス' },
                { key: 'effective_date', label: '適用開始日' },
            ],
        };
    },
    computed: Object.assign({}, mapState(['applications', 'pagination', 'loading', 'error']), {
        canEdit() { return (this.$store.state.session || {}).can_write_applications; },
        currentPage() { return this.pagination.current_page || 1; },
        lastPage() { return this.pagination.last_page || 1; },
        perPage() { return this.pagination.per_page || 10; },
        total() { return this.pagination.total || 0; },
        from() { return this.pagination.from || 0; },
        to() { return this.pagination.to || 0; },
        tableRows() {
            return this.applications.map(application => Object.assign({}, application, {
                coverage: `${Number(application.coverage_amount).toLocaleString('ja-JP')} ${application.currency}`,
                status_label: statusLabels[application.status] || application.status,
            }));
        },
    }),
    created() {
        this.loadCurrentPage();
    },
    methods: {
        loadCurrentPage() {
            return this.$store.dispatch('fetchApplications').catch(() => {});
        },
        search() {
            this.$store.dispatch('fetchApplications', {
                search: this.searchText.trim(),
                status: this.statusFilter,
                per_page: this.perPage,
                page: 1,
            }).catch(() => {});
        },
        reset() {
            this.searchText = '';
            this.statusFilter = '';
            this.$store.dispatch('fetchApplications', { search: '', status: '', per_page: 10, page: 1 }).catch(() => {});
        },
        goToPage(page) {
            this.$store.dispatch('fetchApplications', { page }).catch(() => {});
        },
        changePageSize(size) {
            this.$store.dispatch('fetchApplications', { per_page: Number(size), page: 1 }).catch(() => {});
        },
    },
};
</script>
