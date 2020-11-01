import Vue from 'vue';
import axios from "axios";

let nbTracksState = { inputVal: 1 };

Array.prototype.unique = function() {
    var a = this.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j])
                a.splice(j--, 1);
        }
    }

    return a;
};

setTimeout(function() { 
  Vue.component('artist-item', {
  	props: {
  	  artist: Object,
  	},
  	template: `<div class="artistBloc artistFollowedBloc">
    	  <img v-bind:src="artist.images[0].url" />
    	  <br>
    	  {{ artist.name }} 
  	  </div>`,
  })
  
  var app = new Vue({
    el: '#app',
    delimiters: ['${', '}'],
    data: {
      vueArtists: vueArtists,
      saveVueArtists: this.vueArtists,
      genres: genres,
      selectedGenres: [],
      unwantedGenres: [],
      url: url,
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
      addSelectedGenres: function(event) {
        this.selectedGenres.push(event.srcElement.dataset.name);
        this.refreshVueArtists();
      },
      addUnwantedGenres: function(event) {
        this.unwantedGenres.push(event.srcElement.dataset.name);
        this.refreshVueArtists();
      },
      // Rafraichis les artistes en fonction des filtres
      refreshVueArtists: function() {
        this.vueArtists = this.refreshVueArtistsByUnwantedGenres(this.refreshVueArtistsByGenres());
      },
      // Ressort les artistes qui ont les genres sélectionnés
      refreshVueArtistsByGenres: function() {
        return this.saveVueArtists.filter(function(artist) {
          for (const genre of artist.genres) {
            if (app.selectedGenres.includes(genre)) {
              return true;
            }
          }
          return false;
        });
      },
      // Ressort les artistes qui n'ont pas les genres sélectionnés
      refreshVueArtistsByUnwantedGenres: function(filterdArtists) {
        return filterdArtists.filter(function(artist) {
          for (const genre of artist.genres) {
            if (app.unwantedGenres.includes(genre)) {
              return false;
            }
          }
          return true;
        });
      }
    } 
  })
}, 50);

setTimeout(function() { 
	$('.artists_followed .nice-select .option').click(function() {
		nbTracksState.inputVal = $(this).attr('data-value');
	});
	
	var displayLink = $('.displayAll');
	var hideLink    = $('.hideAll');
	var artists     = $('.artistsFollowed');
	// Liens afficher/cacher tous les artistes
	displayLink.click(function() {
	  artists.addClass('displayAll');
	  $(this).addClass('d-none');
	  hideLink.removeClass('d-none');
	});
	hideLink.click(function() {
	  artists.removeClass('displayAll');
	  $(this).addClass('d-none');
	  displayLink.removeClass('d-none');
	});
}, 100);
