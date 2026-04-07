describe('Authentication & Profile Flows', () => {
    
    // ⚠️ UPDATE THESE to match actual accounts in your test database!
    const adminUser = 'adminto';       
    const adminPass = 'adminto';    
    const staffUser = 'staffto';       
    const staffPass = 'staffto';

    it('Shows an error message for invalid credentials', () => {
        cy.login('fake_user', 'wrong_password');
        // Looks for the error paragraph in your login.php
        cy.get('.error').should('be.visible'); 
    });

    it('Logs in as Admin and redirects to Admin Dashboard', () => {
        cy.login(adminUser, adminPass);
        cy.url().should('include', 'pages/admin-dashboard.php');
    });

    it('Logs in as Staff, tests the Profile Dropdown, Password Modal, and Logout', () => {
        cy.login(staffUser, staffPass);
        cy.url().should('include', 'pages/staff-dashboard.php');

        // 1. Test the Profile Wrapper click
        cy.get('#profileWrapper').click();
        cy.get('#profileDropdown').should('have.class', 'show');

        // 2. Test opening the Change Password Modal
        cy.get('#changePasswordBtn').click();
        cy.get('#changePasswordModal').should('have.class', 'show');

        // 3. Close the modal by clicking outside of it (the overlay)
        cy.get('#changePasswordModal').click(10, 10); // Clicks the top-left corner of the dark overlay
        cy.get('#changePasswordModal').should('not.have.class', 'show');

        // 4. Open dropdown again to test Logout
        cy.get('#profileWrapper').click();
        cy.get('#logoutDropdownBtn').click();

        // 5. Verify the custom Popup.js warning appears
        cy.contains('Confirm Logout').should('be.visible');
        
        // 6. Click the OK button inside the popup
        cy.get('.popup-actions button').contains('OK').click();
        
        // 7. Verify we are kicked back to the login page
        cy.url().should('include', 'public/login.php');
    });
});