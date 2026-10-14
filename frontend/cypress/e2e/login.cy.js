describe('登录页', () => {
    it('空表单显示服务端校验错误', () => {
        cy.visit('/login');
        cy.contains('h1', '管理画面ログイン').should('be.visible');
        cy.get('form').should('have.attr', 'novalidate');

        cy.intercept('POST', '/api/v1/auth/login').as('login');
        cy.contains('button', 'ログイン').click();
        cy.wait('@login').its('response.statusCode').should('eq', 422);
        cy.get('.auth-field-error').first().should('be.visible');
    });
});
