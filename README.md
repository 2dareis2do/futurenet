# FizzBuzz++

`cd` to `fizzbuzz++` directory and install any dependencies:

```
npm install
```

## Usage

```
node app.js
```

It is also possible to run script to output to a log file using the following
syntax (however this means it will not also output to the command line).

```
node app.js >> fizzbuzz.log
```

# Tabular Data View

## About

This is a React Table Component that I have put together using javascript and
ES6 based React. It utilises the Axios module to set the initial state of the
application. This endpoint is passed down to the component using props.

## Installation

This component has been developed with node js and utilises various npm modules.
Just be sure to have [Node](http://nodejs.org/) installed first. Then simply
clone the repo as usual and then use:

    $ npm install

This should install any required dependencies needed in order to run the code.

I have set up this demo to use webpack. Webpack is used to transpile all the
required code to run in a clients machine. To run:

    $ NODE_ENV=production node_modules/.bin/webpack -p

This component should be available localhost on port 8080 i.e. http://localhost:8080

    $ ./node_modules/.bin/http-server static

# Running on your Web Server

This has been set up to run serverside as well. To run in serverside mode

    $ NODE_ENV=production ./node_modules/babel-cli/bin/babel-node.js --presets 'react,es2015' src/server.js

I have also created a wrapper for this that makes it easier to deploy on server.
To run simply:

	$ node server-wrapper.js

If this runs ok, you should be able to view at localhost:8123. Please feel free
to change your port within server.js to suit

## Next Steps

* Unit testing/Linting
* Suggestions from you

# Parser / Transformer

## About

The path in the script is currently set to:

```
$parserFilesPath = "./parser_test/";
```

Make sure that the folder you would like to recurse the log files
has this name and path relative to the script path. Alternatively you can change
the path of the script.

Script is simply run:

```
php index.php
```
The script recursively iterates over directory to find any log files and then
proceeds to transform into a csv file.
