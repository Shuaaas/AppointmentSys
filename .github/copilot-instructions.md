# AI Development Rules (MANDATORY)

Before implementing ANY request, you MUST follow these rules.

## 1. Preserve the Existing Project Architecture
- ALWAYS analyze the current project structure before making changes.
- STRICTLY follow the existing folder architecture, naming conventions, coding style, and design patterns already used in the project.
- Do NOT create new folders, files, classes, or modules unless they are absolutely required.
- Reuse existing components, services, controllers, managers, utilities, and resources whenever possible.
- Keep the project organized according to its current architecture.

## 2. Make the Smallest Possible Change
- Only modify the files directly related to the requested feature or bug.
- Never rewrite an entire file when only a few lines need to change.
- Avoid unnecessary refactoring.
- Do not "improve" unrelated code.
- Keep changes as minimal and isolated as possible.

## 3. Do Not Break Existing Functionality
- Treat all existing features as production-ready unless explicitly instructed otherwise.
- Never remove or modify existing functionality unless it is required for the requested feature.
- Ensure all existing workflows continue to work exactly as before.
- Avoid introducing regressions.

## 4. Respect Existing Dependencies
- Do not replace existing libraries or frameworks.
- Do not install new packages unless they are absolutely necessary and approved.
- Reuse the project's current technologies.

## 5. Preserve Existing Logic
- Do not change business logic unrelated to the request.
- Do not rename routes, functions, variables, classes, database tables, or APIs unless explicitly requested.
- Keep compatibility with the existing codebase.

## 6. Modify Only When Necessary
If a requested feature requires changes:
- Identify the minimum set of files that need modification.
- Modify only those files.
- Leave all unrelated files untouched.
- If a file does not need changes, do not edit it.

## 7. Before Writing Code
First analyze:
- The current architecture.
- Existing implementations.
- Related controllers/services/managers.
- Existing helper functions.
- Current coding conventions.

Then determine the safest implementation that requires the fewest changes.

## 8. Code Quality
All new code must:
- Match the existing coding style.
- Be clean and maintainable.
- Avoid duplicate code.
- Reuse existing methods whenever possible.
- Include proper error handling.
- Be consistent with the rest of the project.

## 9. Safety Rule
If there are multiple ways to implement a feature:
- Choose the solution that introduces the least risk.
- Prefer extending existing code over creating new code.
- Never modify unrelated modules simply because they can be improved.

## 10. Completion Checklist
Before considering the task complete, verify:
- ✓ The current folder architecture is preserved.
- ✓ Only necessary files were modified.
- ✓ Existing functionality remains unchanged.
- ✓ No unrelated code was edited.
- ✓ No unnecessary refactoring was performed.
- ✓ No duplicate logic was introduced.
- ✓ The implementation follows the project's existing architecture and conventions.

These rules are mandatory and take precedence over optimization or refactoring suggestions unless the user explicitly requests otherwise.
