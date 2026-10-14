const vueJest = require('vue-jest');

// Vue scripts are instrumented by the test-only Babel plugin.
// Prevent Jest from instrumenting the compiled SFC again and losing source locations.
module.exports = { ...vueJest, canInstrument: true };
