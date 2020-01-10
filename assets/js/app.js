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
const $ = require('jquery');

Vue.component('sidebar-artist', {
	props: ['html', 'initUrl'],
	template: '<a class="artistBloc" v-html="html"></a>'
})

var app = new Vue({
    delimiters: ['${', '}'],
    el: '#app',
    data: {
        message: '',
        artists: [],
    },
    methods: {
        addToSelection: function(urlAddToSelection, data, event) {
            axios.post(urlAddToSelection, {
				body: data
			})
            // this.artists.push(event.currentTarget.innerHTML);
            this.addArtists(event.currentTarget.innerHTML);
        },
        addArtists: function(htmlContent) {
        	this.artists.unshift(htmlContent);
        },
		emptySelection: function(urlEmpty) {
			this.artists = [];
			$('.sidebar-left .artistBloc').each(function() {
				this.remove();
			});
			axios.get(urlEmpty);
		}
    }
})
