describe('Authentication Flow', () => {
  it('successfully logs in an admin and then logs out', () => {
    // Visit the login page
    cy.visit('/login.php');

    // Type in credentials (adjust the input names to match your HTML)
    cy.get('input[name="username"]').type('admin_username'); 
    cy.get('input[name="password"]').type('admin_password');

    // Click the login button
    cy.get('button[type="submit"]').click();

    // Verify the browser redirected to the admin dashboard
    cy.url().should('include', 'admin-dashboard.php');

    // Test the shredder! Click the toggle, then logout
    cy.get('#toggleSidebar').click();
    cy.get('#logoutBtn').click();
    
    // Cypress can handle JS alerts/popups. If you have a custom DOM popup, click its OK button:
    // cy.get('.popup-ok-btn').click(); 

    // 6. Verify we are locked out and back at the login screen
    cy.url().should('include', 'login.php');
  });
});