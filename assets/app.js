/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

var $ = require("jquery");
//yarn bootstrap
import 'bootstrap';
//bootstrap.js
import './bootstrap';
global.$ = global.jQuery = $;
// import { Application } from 'stimulus';
//  import { definitionsFromContext } from 'stimulus/webpack-helpers'; 
//  const application = Application.start(); 
//  const context = require.context('./controllers', true, /\.js$/); 
//  application.load(definitionsFromContext(context));
// console.log("project o");
// // start the Stimulus application

$(document).ready(function () {
    $('[data-toggle="popover"]').popover();
});

let globle_loader = document.getElementById('globle_loader');
jQuery(document).ready(function ($) {
    $(".clickable-row").click(function () {
        window.location = $(this).data("href");
    });
    globle_loader.classList.add("d-none");
});

