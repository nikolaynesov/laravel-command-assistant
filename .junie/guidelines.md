# Project Guidelines

This is a Laravel Plugin. 

## Test Writing Guidelines

These are standardized guidelines for writing and structuring tests. Use them when generating new tests or reviewing existing ones.
We use Pest for testing.

---

### Test Folder Structure

Test folder structure should replicate the application folder structure.

---

### Best Practices

- Each method should have **one test**.
- When it's possible to create a real object - use it instead of creating a mock.
- For API tests:
    - Use named routes via `route('...')`

### Making sure tests pass
- Always make sure tests pass before completing the task
- By default, we assume that the class we are testing is correct and if test fails - the test should be fixed. However, if you strongly suspect that the issue is in the tested code, not in the test itself - always ask confirmation before making changes to the code that we are writing tests for.
