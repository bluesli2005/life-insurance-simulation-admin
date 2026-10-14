#!/usr/bin/env python3
"""Check staged-file protection, failed checks, and missing coverage handling."""
import importlib.util
import json
from pathlib import Path
import shutil
import subprocess
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[1]
spec = importlib.util.spec_from_file_location('coverage_summary', ROOT / 'scripts/coverage-summary.py')
coverage = importlib.util.module_from_spec(spec)
spec.loader.exec_module(coverage)


class CiChecksTest(unittest.TestCase):
    def test_hook_checks_only_matching_staged_files_and_propagates_failure(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            subprocess.run(['git', 'init', '-q', str(root)], check=True)
            (root / '.githooks').mkdir()
            shutil.copy(ROOT / '.githooks/pre-commit', root / '.githooks/pre-commit')
            (root / 'scripts').mkdir()
            (root / 'scripts/check.sh').write_text('echo CHECK_RAN\nexit "${CHECK_EXIT:-0}"\n')
            (root / 'backend').mkdir()
            source = root / 'backend/example.php'
            source.write_text('<?php echo 1;\n')
            subprocess.run(['git', '-C', temp, 'add', '.'], check=True)
            def hook():
                return subprocess.run(['sh', '.githooks/pre-commit'], cwd=temp, capture_output=True, text=True)
            source.write_text('<?php echo 2;\n')
            result = hook()
            self.assertNotEqual(0, result.returncode)
            self.assertNotIn('CHECK_RAN', result.stdout)
            subprocess.run(['git', '-C', temp, 'add', '.'], check=True)
            result = hook()
            self.assertEqual(0, result.returncode, result.stderr)
            self.assertIn('CHECK_RAN', result.stdout)
            (root / 'scripts/check.sh').write_text('echo CHECK_RAN\nexit 1\n')
            subprocess.run(['git', '-C', temp, 'add', '.'], check=True)
            self.assertNotEqual(0, hook().returncode)

    def test_coverage_totals_and_missing_report_are_explicit(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            text, complete = coverage.summary(root)
            self.assertFalse(complete)
            self.assertIn('Not generated', text)
            frontend = root / 'frontend/coverage'
            frontend.mkdir(parents=True)
            total = {k: {'covered': 1, 'total': 2} for k in ['lines', 'statements', 'branches', 'functions']}
            (frontend / 'coverage-summary.json').write_text(json.dumps({'total': total}))
            backend = root / 'backend/coverage'
            backend.mkdir(parents=True)
            (backend / 'clover.xml').write_text('<coverage><project><metrics coveredstatements="3" statements="4" coveredmethods="1" methods="2" /></project></coverage>')
            text, complete = coverage.summary(root)
            self.assertTrue(complete)
            self.assertIn('50.00% (1/2)', text)
            self.assertIn('75.00% (3/4)', text)


if __name__ == '__main__':
    unittest.main()
