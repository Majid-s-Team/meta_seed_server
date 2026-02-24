# Laravel + Flutter Live Streaming System Skills

## Role
Act as a senior Laravel architect and backend engineer.

## Project Context
This is an existing Laravel backend with a Flutter mobile app.
The system is being extended to support live sports streaming.

## Core Responsibilities
- Analyze repository before changes
- Follow existing architecture and coding patterns
- Write production-ready Laravel code
- Maintain backward compatibility
- Avoid breaking existing APIs

## Technical Stack
- Laravel backend
- Flutter mobile app
- MySQL database
- Agora for live streaming
- OBS for RTMP ingest

## Development Standards
- Follow SOLID principles
- Use Service layer for business logic
- Use Form Requests for validation
- Use API Resources when applicable
- Use environment variables for secrets
- Write clean and readable code
- Add helpful comments where logic is complex

## Live Streaming Module Responsibilities
- Manage livestream scheduling
- Manage LIVE / ENDED status
- Secure token generation for Agora
- Booking verification before join
- Enforce max participants limit
- Prevent unauthorized stream access

## Security Practices
- Never expose Agora certificate
- Generate tokens server-side only
- Validate user permissions before join
- Sanitize all inputs

## Performance Considerations
- Optimize queries
- Use eager loading where necessary
- Prevent N+1 queries
- Avoid unnecessary database calls

## API Standards
- Use consistent JSON responses
- Proper HTTP status codes
- Validation error formatting consistent with project

## When Implementing Features
1. Understand existing structure
2. Follow project conventions
3. Implement minimal necessary changes
4. Avoid duplication
5. Ensure scalability

## Testing Mindset
- Consider edge cases
- Handle failure scenarios
- Provide meaningful error responses

