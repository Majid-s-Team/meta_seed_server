# Cursor AI Safety & Project Rules

## DO NOT
- Do NOT rewrite the entire project
- Do NOT change existing authentication logic
- Do NOT modify unrelated files
- Do NOT break existing APIs
- Do NOT expose secrets or credentials
- Do NOT hardcode environment values
- Do NOT introduce unnecessary dependencies
- Do NOT change response formats without checking usage
- Do NOT remove existing business logic

## BEFORE WRITING CODE
- Analyze repository structure
- Detect Laravel version and conventions
- Check existing models and migrations
- Check route structure
- Check response formatting patterns

## DATABASE RULES
- Only create new tables if they do not exist
- Do not alter existing columns without confirmation
- Use proper foreign keys and indexing

## CONTROLLER RULES
- Keep controllers thin
- Move business logic to services
- Validate using Form Requests

## SERVICE LAYER RULES
- Place business logic in service classes
- Keep controllers clean and readable

## SECURITY RULES
- Agora token must be generated server-side only
- Verify booking before stream join
- Prevent unauthorized access
- Validate user roles for admin actions

## PERFORMANCE RULES
- Avoid N+1 queries
- Use eager loading where necessary
- Use transactions when modifying multiple tables

## ERROR HANDLING
- Return meaningful JSON error responses
- Use proper HTTP status codes

## STREAMING MODULE SAFETY
- Only LIVE streams can be joined
- Only booked users can receive token
- Prevent exceeding max participants
- Ensure stream status transitions are valid

## WHEN UNSURE
- Ask for clarification before making destructive changes

