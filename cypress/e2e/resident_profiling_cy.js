describe('Resident Management', () => {
  beforeEach(() => {
    // Cypress needs to be logged in before it can test protected pages
    cy.visit('/login.php');
    cy.get('input[name="username"]').type('admin_username');
    cy.get('input[name="password"]').type('admin_password');
    cy.get('button[type="submit"]').click();
  });

  it('adds a new resident successfully', () => {
    cy.visit('/resident-profiling.php');

    // 1. Open the modal
    cy.get('.add-tag').click(); // Adjust this selector to match your "Add Resident" button class/id

    // 2. Fill out the form
    cy.get('input[name="first_name"]').type('Juan');
    cy.get('input[name="last_name"]').type('Dela Cruz');
    cy.get('input[name="address"]').type('Block 1, Lot 2');
    // ... add other required fields ...

    // 3. Submit the form
    cy.get('form#addResidentForm').submit();

    // 4. Verify the success alert popped up
    cy.on('window:alert', (text) => {
      expect(text).to.contains('successfully');
    });
  });
});