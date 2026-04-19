# Contributing to Agent Pay System

Even if you're working alone, follow these guidelines to maintain framework quality.

## Adding New Core Features

1. Discuss architecture impact
2. Avoid breaking container contracts
3. Prefer Service Providers
4. Keep backward compatibility
5. Test integration thoroughly
6. Update documentation (AGENTS.md, ARCHITECTURE.md)

## Code Style

- PSR-4 autoloading
- Dependency Injection everywhere
- No static calls outside Facades
- Framework-agnostic core

## Testing

- Unit tests for core components
- Integration tests for features
- Manual testing for UI changes

## Documentation

- Update AGENTS.md for new patterns
- Update ARCHITECTURE.md for new layers
- Keep FRAMEWORK_BOUNDARY.md current
