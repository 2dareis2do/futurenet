#!/usr/bin/env node

const log4js = require("log4js");
const logger = log4js.getLogger();
logger.level = "debug";

log4js.configure({
  appenders: {
    output: { type: 'file', filename: 'fizzbuzz.log', layout: { type: 'messagePassThrough' } },
    'out': { type: 'stdout', layout: { type: 'basic' } }
  },
  categories: { default: { appenders: ['output'], level: 'info' } }
});

function isPrime(num) {
  if (num < 2) return false;

  for (let k = 2; k < num; k++) {
    if (num % k == 0) {
      return false;
    }
  }
  return true;
}

function fizzBuzz(n) {
  // The script should echo the numbers 1 to 500, each number being on a new line.
  console.time("FizzBuzz++ 1-500");
  for (i = 1; i <= n; i++) {
    if (isPrime(i)) {
      console.log(i + " FizzBuzz++");
      logger.info(i + " FizzBuzz++");
    }
    else {
      if (i % 5 === 0 && i % 3 === 0) {
        console.log(i + " FizzBuzz");
        logger.info(i + " FizzBuzz");
      } else {
        if (i % 3 === 0) {
          console.log(i + " Fizz");
          logger.info(i + " Fizz");
        }
        else {
          if (i % 5 === 0) {
            console.log(i + " Buzz");
            logger.info(i + " Buzz");
          }
          else {
            console.log(i);
            logger.info(i);
          }
        }

      }
    }
  }
  console.timeEnd("FizzBuzz++ 1-500");
}

fizzBuzz(500);
