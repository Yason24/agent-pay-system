# Framework Boundary

Framework code lives in /framework

Application code lives in /app

Rules:

- Framework MUST NOT depend on App namespace.
- App MAY depend on Framework.
- Providers bridge Framework and App layers.
