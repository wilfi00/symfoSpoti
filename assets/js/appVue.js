import Vue from 'vue';
import axios from "axios";

let nbTracksState = { inputVal: 1 };

setTimeout(function() { 
  Vue.component('artist-item', {
  	props: ['artist'],
  	template: '<div class="artistBloc artistFollowedBloc"><img v-bind:src="artist.images[0].url" /><br>{{ artist.name }} <br> Popularité : {{ artist.popularity }} <br> {{ artist.genres }}</div>'
  })
  
  var app7 = new Vue({
    el: '#app',
    data: {
      vueArtists: vueArtists,
      url: url,
      seen: true,
      nbTracksState,
      playlistName: '',
    },
    methods: {
      submitData: function () {
        axios
          .post(this.url, {
            artists: this.vueArtists,
            nbTracks: nbTracksState.inputVal,
            playlistName: this.playlistName
          })
          .then(
            //showLoader
          )
          .done(
            showLoader()
          )
      },
    } 
  })
}, 50);

setTimeout(function() { 
	$('.artists_followed .nice-select .option').click(function() {
		nbTracksState.inputVal = $(this).attr('data-value');
	});
}, 100);
