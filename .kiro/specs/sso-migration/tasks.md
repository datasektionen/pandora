# Implementation Plan

- [x] 1. Set up SSO configuration and service foundation
  - Create SSO configuration file with environment variable mappings
  - Create base SSO service class with OpenID Connect client initialization
  - Add required environment variables to .env.example
  - _Requirements: 4.1, 4.2, 4.4_

- [x] 2. Implement core SSO authentication service
  - Create SSOService class with getAuthorizationUrl() method for generating OIDC authorization URLs
  - Implement handleCallback() method to exchange authorization code for tokens
  - Add extractUserInfo() method to parse user data from ID token claims
  - Add extractPermissions() method to parse custom permissions claim
  - _Requirements: 1.1, 1.3, 1.4, 2.2, 2.3_

- [x] 3. Create permission management service
  - Create PermissionService class with storePermissions() method for session storage
  - Implement hasPermission() method to check specific permissions with scope matching
  - Add getEntitiesForPermission() method to retrieve accessible entities
  - Add isSuperAdmin() method to check for admin permission
  - _Requirements: 2.4, 2.5, 2.6, 2.7, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

- [x] 4. Update User model with new authentication methods
  - Modify User model to add sso_subject field for SSO user identification
  - Update isAdmin() method to use PermissionService for admin check
  - Update isSomeAdmin() method to check for any permissions in session
  - Update isAdminFor() method to use manage permission with entity scope
  - _Requirements: 1.5, 3.4, 3.7_

- [x] 5. Implement new authentication controller methods
  - Update AuthController getLogin() method to redirect to SSO authorization URL
  - Create getCallback() method to handle SSO callback and complete authentication
  - Update getLogout() method to clear session and redirect to SSO logout endpoint
  - Add error handling for authentication failures and invalid tokens
  - _Requirements: 1.1, 1.6, 5.1, 5.2, 5.3, 5.4_

- [x] 6. Update authorization middleware classes
  - Update Admin middleware to use PermissionService for admin check
  - Update IsSomeAdmin middleware to check for any permissions using PermissionService
  - Update IsAdminForEntity middleware to use manage permission with entity scope
  - Update IsAdminForEvent middleware to use manage permission with event entity scope
  - _Requirements: 3.7_

- [x] 7. Update Entity model for new permission system
  - Modify Entity::forAuthUser() method to use PermissionService for entity access
  - Update method to handle manage permissions with scope matching
  - Add support for wildcard (*) scope permissions
  - _Requirements: 3.5, 3.6_

- [x] 8. Update authentication routes and remove legacy endpoints
  - Update web.php routes to use new AuthController callback method
  - Add new /auth/callback route for SSO callback handling
  - Remove old login-complete route and related legacy authentication routes
  - Update route middleware to ensure proper authentication flow
  - _Requirements: 6.1, 6.3_

- [x] 9. Add database migration for User model updates
  - Create migration to add sso_subject column to users table
  - Add index on sso_subject for efficient user lookups
  - Write migration to handle existing user data if needed
  - _Requirements: 1.5_

- [x] 10. Implement comprehensive error handling and logging
  - Add error handling for SSO service unavailability with user-friendly messages
  - Implement logging for authentication events and permission checks
  - Add validation for SSO configuration on application startup
  - Create custom exception classes for SSO-related errors
  - _Requirements: 4.5, 4.6_

- [x] 11. Remove legacy authentication code and dependencies
  - Remove old login/login2 integration code from AuthController
  - Remove PLS API integration code and session handling
  - Clean up old environment variables from configuration
  - Remove unused authentication-related helper methods
  - Update composer.json to remove any legacy authentication dependencies if applicable
  - _Requirements: 6.1, 6.2, 6.3, 6.5_
