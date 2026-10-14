const path = require('path');
const vm = require('vm');
const transformer = require('../../../scripts/jest-vue-transformer');

test('Vue coverage records executed and unexecuted source methods', () => {
    const filename = path.resolve(__dirname, '../coverage-probe.vue');
    const source = `<template><div /></template>
<script>
export default {
    methods: {
        called: function called() { return 1; },
        neverCalled: function neverCalled() { return 2; },
    },
};
</script>`;
    const result = transformer.process(source, filename, { rootDir: process.cwd(), globals: {} });
    const module = { exports: {} };
    const context = { module, exports: module.exports };
    vm.runInNewContext(result.code, context);
    module.exports.default.methods.called();

    const coverage = context.__coverage__[filename];
    const methodId = name => Object.keys(coverage.fnMap).find(id => coverage.fnMap[id].name === name);
    expect(methodId('called')).toBeDefined();
    expect(methodId('neverCalled')).toBeDefined();
    expect(coverage.f[methodId('called')]).toBe(1);
    expect(coverage.f[methodId('neverCalled')]).toBe(0);
});
