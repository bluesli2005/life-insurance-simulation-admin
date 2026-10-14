#!/usr/bin/env python3
"""Print coverage totals and add them to the GitHub Actions job summary."""
import json
import os
from pathlib import Path
import sys
import xml.etree.ElementTree as ET


def percentage(covered, total):
    return f"{covered / total * 100:.2f}% ({covered}/{total})" if total else "N/A (0/0)"


def summary(root):
    rows = []
    frontend = root / 'frontend/coverage/coverage-summary.json'
    backend = root / 'backend/coverage/clover.xml'
    if frontend.is_file():
        total = json.loads(frontend.read_text())['total']
        values = [percentage(total[k]['covered'], total[k]['total'])
                  for k in ['lines', 'statements', 'branches', 'functions']]
        rows.append('| Frontend (Jest) | ' + ' | '.join(values) + ' |')
    else:
        rows.append('| Frontend (Jest) | Not generated | — | — | — |')
    if backend.is_file():
        metrics = ET.parse(backend).getroot().find('project/metrics').attrib
        lines = percentage(int(metrics['coveredstatements']), int(metrics['statements']))
        methods = percentage(int(metrics['coveredmethods']), int(metrics['methods']))
        rows.append(f'| Backend (PHPUnit) | {lines} | — | — | {methods} |')
    else:
        rows.append('| Backend (PHPUnit) | Not generated | — | — | — |')
    text = '\n'.join([
        '# Coverage', '',
        '| Component | Lines | Statements | Branches | Functions / Methods |',
        '| --- | --- | --- | --- | --- |', *rows, '',
        'PHP PCOV provides line coverage; PHP branch coverage is not collected.', '',
    ])
    return text, frontend.is_file() and backend.is_file()


if __name__ == '__main__':
    root = Path(sys.argv[1]) if len(sys.argv) > 1 else Path(__file__).resolve().parents[1]
    text, complete = summary(root)
    output = root / 'coverage/summary.md'
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(text)
    print(text)
    if os.environ.get('GITHUB_STEP_SUMMARY'):
        with open(os.environ['GITHUB_STEP_SUMMARY'], 'a') as handle:
            handle.write(text)
    sys.exit(0 if complete else 1)
