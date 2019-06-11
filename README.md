# Greenpeace Planet 4 Engaging Networks Plugin

![Planet4](./planet4.png)

## Introduction

This Wordpress plugin connects Planet 4 with the Engaging Networks platform.

## Task automation
We use gulp as automation tool for local development.

Available tasks

* `gulp git_hooks` 'copies repo's git hooks to local git repo'
* `gulp lint_css` 'checks for css/sass lint errors'
* `gulp lint_js` 'checks for js lint errors'
* `gulp sass` 'concatanates/compiles sass files into a minified single stylesheet'
* `gulp uglify` 'concatanates/mangles js files into a minified single js file'
* `gulp watch` 'watches for changes in js or sccs files and runs the minification tasks. Can be used in conjuction with livereload to reload browser automatically on changes'
https://www.npmjs.com/package/livereload#usage

## Contribute

Please read the [Contribution Guidelines](https://planet4.greenpeace.org/handbook/dev-contribute-to-planet4/) for Planet4.
