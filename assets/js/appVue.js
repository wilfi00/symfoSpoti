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
  	template: `<div class="artistBloc artistFollowedBloc" :class="{ 'disabled' : artist.active == false}">
    	  <img v-bind:src="artist.images[0].url" />
    	  <br>
    	  {{ artist.name }} 
  	  </div>`,
  })
  
  app = new Vue({
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
      active: true,
      nbActiveArtists: this.vueArtists.length
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
      deleteSelectedGenre: function(genreName) {
        this.selectedGenres.remove(genreName);
        this.refreshVueArtists();
      },
      addUnwantedGenres: function(event) {
        this.unwantedGenres.push(event.srcElement.dataset.name);
        this.refreshVueArtists();
      },
      // Rafraichis les artistes en fonction des filtres
      refreshVueArtists: function() {
        this.refreshVueArtistsByGenres();
        //this.vueArtists = this.refreshVueArtistsByUnwantedGenres(this.refreshVueArtistsByGenres());
      },
      // Ressort les artistes qui ont les genres sélectionnés
      refreshVueArtistsByGenres: function() {
        this.nbActiveArtists = 0;
        // Active à true pour les genres sélectionnés
        this.vueArtists.forEach(function(artist) {
          if (app.selectedGenres.length <= 0) {
            artist.active = true;
            app.nbActiveArtists++;
            return;
          }
          
          artist.active = false;
          for (const genre of artist.genres) {
            if (app.selectedGenres.includes(genre)) {
              app.nbActiveArtists++;
              artist.active = true;
              return;
            }
          }
        });
        // Tri pour mettre les active en premier
        this.vueArtists.sort(function(a, b) {
          if (a.active && !b.active) {
            return -1;
          } else if (b.active && !a.active) {
            return 1;
          }
          
          if ((a.active && b.active) || (!a.active && !b.active)) {
            if (a.name < b.name) {
              return -1;
            } else {
              return 1;
            }
          }
        });
      }
    } 
  })
}, 50);
