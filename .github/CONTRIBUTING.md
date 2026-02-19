# Contributing

Contributions are **welcome** and will be credited.

We accept contributions via Pull Requests on [Github](https://github.com/NMe84/watch-jelly-together).
Do [PHP the Right Way](http://www.phptherightway.com/), keep the PHPstan level maxed, include tests with
proper coverage, and run `bin/prepare-commit` during development and before committing.

Infection testing is by default only done on **changed** files. It is *recommended* to run
`bin/infection` before finishing a PR to evaluate infection status of the entire project.

We will not accept pull requests that lower the code coverage or infection status of the project, so
please make sure to run `bin/prepare-commit` before submitting a PR.
