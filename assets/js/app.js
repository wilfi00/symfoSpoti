/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');
import Vue from "vue";
import axios from "axios";

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

Vue.component('sidebar-artist', {
	props: ['html'],
	template: '<a class="artistBloc" v-html="html"></a>'
	 // template: '<a class="artistBloc">{{ test }}</a>'
})

var app = new Vue({
    delimiters: ['${', '}'],
    el: '#app',
    data: {
        message: '',
        artists: [],
        testvar: ''
    },
    // props: ['url'],
    beforeMount: function() {
    	console.log(Routing.generate('getArtists'));
    	// console.log(this.url);
    	this.addArtists('html artiste lol');
        // this.artists = axios.get(urlAddToSelection)
    },
    methods: {
        addToSelection: function(urlAddToSelection, event) {
            axios.get(urlAddToSelection)
            // this.artists.push(event.currentTarget.innerHTML);
            this.addArtists(event.currentTarget.innerHTML);
        },
        addArtists: function(htmlContent) {
        	this.artists.push(htmlContent);
        }
    }
})
