# Requirements Document

## Introduction

This feature migrates the booking system from the current custom authentication system (`login/login2` and `pls`) to the new internal SSO system that uses standard OpenID Connect. The migration will replace the existing authentication flow while maintaining all current authorization functionality through the new permission-based system.

The current system authenticates users via `login.datasektionen.se` and authorizes them through the `pls` service, storing permissions in the session. The new system will use OpenID Connect with a custom `permissions` scope that provides structured permission data directly in the user claims.

## Requirements

### Requirement 1

**User Story:** As a user, I want to authenticate using the new SSO system, so that I can access the booking system with a modern, standardized authentication flow.

#### Acceptance Criteria

1. WHEN a user visits the login page THEN the system SHALL redirect them to the SSO OpenID Connect authorization endpoint
2. WHEN the SSO system redirects back with an authorization code THEN the system SHALL exchange it for tokens using the OpenID Connect flow
3. WHEN tokens are successfully obtained THEN the system SHALL extract user information from the ID token
4. WHEN user information is extracted THEN the system SHALL create or update the user record in the local database
5. IF the user does not exist locally THEN the system SHALL create a new user record with information from the SSO claims
6. WHEN authentication is complete THEN the system SHALL redirect the user to their intended destination or the home page

### Requirement 2

**User Story:** As a user, I want my permissions to be automatically loaded from the SSO system, so that I don't need separate authorization calls to the old `pls` system.

#### Acceptance Criteria

1. WHEN requesting tokens from SSO THEN the system SHALL include the `permissions` scope in the authorization request
2. WHEN the ID token is received THEN the system SHALL extract the `permissions` claim containing the user's permissions array
3. WHEN permissions are extracted THEN the system SHALL store them in the user session for authorization checks
4. WHEN permissions contain objects with `id` and `scope` keys THEN the system SHALL map them to the application's permission model
5. IF a permission has scope `*` THEN the system SHALL treat it as applying to all entities
6. IF a permission has a specific scope value THEN the system SHALL treat it as applying only to that entity
7. IF a permission has scope `null` THEN the system SHALL treat it as a global permission where entity restrictions do not apply

### Requirement 3

**User Story:** As a system administrator, I want the new permission system to provide proper access controls, so that users can only access resources they're authorized for.

#### Acceptance Criteria

1. WHEN checking for booking permissions THEN the system SHALL verify the user has `manage` permission with appropriate scope
3. WHEN checking for entity management permissions THEN the system SHALL verify the user has `admin` permission
4. WHEN a user has `admin` permission THEN the system SHALL grant them super admin access
5. WHEN checking entity-specific permissions THEN the system SHALL match the permission scope against the entity's identifier
6. IF a user has permission scope `*` THEN the system SHALL grant access to all entities for that permission type
7. WHEN implementing authorization middleware THEN the system SHALL use the new permission structure to control access

### Requirement 4

**User Story:** As a developer, I want the authentication system to be configurable, so that it can work in different environments (development, staging, production).

#### Acceptance Criteria

1. WHEN configuring the SSO client THEN the system SHALL read OpenID Connect settings from environment variables
2. WHEN the application starts THEN the system SHALL validate that all required SSO configuration is present
3. WHEN in development mode THEN the system SHALL support alternative SSO endpoints for testing
4. WHEN SSO configuration changes THEN the system SHALL not require code changes, only environment variable updates
5. IF SSO is unavailable THEN the system SHALL provide clear error messages to users
6. WHEN logging authentication events THEN the system SHALL include sufficient detail for debugging without exposing sensitive information

### Requirement 5

**User Story:** As a user, I want to be able to log out properly, so that my session is terminated both locally and with the SSO provider.

#### Acceptance Criteria

1. WHEN a user clicks logout THEN the system SHALL clear the local session data
2. WHEN logging out THEN the system SHALL redirect to the SSO logout endpoint to terminate the SSO session
3. WHEN SSO logout is complete THEN the system SHALL redirect back to the application home page
4. WHEN logout is complete THEN the system SHALL display a confirmation message
5. IF the user accesses protected resources after logout THEN the system SHALL require re-authentication

### Requirement 6

**User Story:** As a system maintainer, I want to cleanly replace the old authentication system, so that the codebase is simplified and uses modern standards.

#### Acceptance Criteria

1. WHEN the new authentication system is implemented THEN the old `login/login2` authentication code SHALL be removed
2. WHEN the new authorization system is implemented THEN the old `pls` integration code SHALL be removed
3. WHEN the migration is complete THEN the old authentication routes and controllers SHALL be removed
4. WHEN the new system is active THEN all users SHALL be required to authenticate through the SSO system
5. WHEN old session data exists THEN the system SHALL clear it and require fresh authentication