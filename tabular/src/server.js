'use strict';

import path from 'path';
import { Server } from 'http';
import Express from 'express';
import React from 'react';
import { renderToString } from 'react-dom/server';
import App from './components/App';

// initialize the server and configure support for ejs templates
const app = new Express();
const server = new Server(app);
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// define the folder that will be used for static assets
app.get('*', Express.static(path.join(__dirname, '../', 'static')));

// universal routing and rendering
app.get('*',(req, res) => {

    let markup;

    let tagline = "Star Wars Characters";

    markup = renderToString(<App/>);

    res.render('index', {
        name: tagline,
        markup: markup
    });

});

// start the server
const port = process.env.PORT || 8123;
const hostName = "127.0.0.1"; 
const env = process.env.NODE_ENV || 'production';
server.listen(port, hostName, err => {
  if (err) {
    return console.error(err);
  }
 console.info(`Server running on http://localhost:${port} [${env}]`);
});
