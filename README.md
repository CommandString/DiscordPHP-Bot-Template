# PackageTemplate

A template repository for making composer packages

## Requirements

- PHP 8.1+

## What's Included

- [Composer Normalize](https://github.com/ergebnis/composer-normalize)
- [Faker](https://github.com/fakerphp/faker)
- [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
- [JetBrains Attributes](https://github.com/jetbrains/phpstorm-attributes)
- [PHPUnit](https://phpunit.de)
- [Roave Security Advisories](https://github.com/Roave/SecurityAdvisories)
- [Symfony VarDumper](https://github.com/symfony/var-dumper)
- [Composer Git Hooks](https://github.com/xheaven/composer-git-hooks)

- GitHub Actions
- Composer Scripts
    - `composer test` - Run PHPUnit tests
    - `composer test:coverage` - Run PHPUnit tests with coverage
    - `composer fix:dry` - Run PHP-CS-Fixer in Dry-Run mode
    - `composer fix` - Run PHP-CS-Fixer and fix errors

## Installation

```bash
composer create-project tnapf/package <package>
```

## Setup GitHub Repository

1. Run `git init` to initialize a new git repository
2. Run `git add .` to add all files to the repository
3. Run `git commit -m "initial commit"` to commit the files
4. Run `git branch -M main` to rename the current branch to `main`
5. Create a new repository on GitHub
6. Run `git remote add origin https://github.com/tnapf/<package>.git` to add the new repository as remote
7. Run `git push -u origin main` to push the files to the new repository
