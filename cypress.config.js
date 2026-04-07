const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    // Correctly mapped to your XAMPP HALO folder
    baseUrl: 'http://localhost/HALO', 
    
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
    defaultCommandTimeout: 6000,
  },
});