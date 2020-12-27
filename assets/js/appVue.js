import Vue from 'vue';
import axios from "axios";

setTimeout(function() { 
  Vue.component('genre-item', {
  	props: {
  	  genre: Object,
  	},
  	template: `<li @click="$emit('clickgenre', genre)" class="genre" v-bind:id="'genre' + genre.id" v-bind:data-id="genre.id">{{ genre.name }}</li>`,
  })
  
  Vue.component('selected-genre-item', {
  	props: {
  	  genre: Object,
  	},
  	template: `<li class="genre" v-bind:id="'genre' + genre.id" v-bind:data-id="genre.id">{{ genre.name }}<button @click="$emit('deletegenre', genre)" type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button></li>`,
  })
  
  /*v-for="item in selectedGenres"
    						v-bind:genre="item"
    						v-bind:key="item.id"
    						@deletegenre="deleteSelectedGenre"
    						
    						<div v-for="(item, index) in mutableOptions">
      <h3>{{ index }}<h3>
      <h4>{{ item.Display }}<h4>
    </div>
    						*/
  
  Vue.component('artist-item', {
  	props: {
  	  artist: Object,
  	},
  	template: `
  	<div class="artistBloc artistFollowedBloc">
    	<div class="popover-content d-none"><div class='genres'><span v-for='(genre, index) in artist.genres' :key='index' class='genre'>{{ genre }}</span></div></div>
      <svg title="Genres de l'artiste" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="picto-info bi bi-info-circle-fill" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM8 5.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
      </svg>
  	  <img :class="{ 'disabled' : artist.active == false}" v-bind:src="artist.images[0].url" />
  	  <br>
	    <p><a v-on:click="$emit('lower-active-artists'); artist.active = false;" v-if="artist.active"><svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-dash-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7z"/>
      </svg></a>
      <a v-if="!artist.active" v-on:click="$emit('increase-active-artists'); artist.active = true;"><svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-plus-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"/>
      </svg></a>
  	  <span :class="{ 'disabled' : artist.active == false}">{{ artist.name }}</span></p>
  	  </div>`,
  })
  
  window.app = new Vue({
    el: '#app',
    delimiters: ['${', '}'],
    data: {
      vueArtists: vueArtists,
      vueGenres: vueGenres,
      activeVueGenres: this.vueGenres,
      selectedGenres: [],
      unwantedGenres: [],
      url: url,
      urlSearchGenre: urlSearchGenre,
      playlistName: '',
      active: true,
      nbActiveArtists: this.vueArtists.length,
      inputSearchGenre: '',
      timer: '',
    },
    methods: {
      searchGenres: function(event) {
        if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
        }
        this.timer = setTimeout(() => {
          axios
            .post(this.urlSearchGenre, {
              search: this.inputSearchGenre,
            })
            .then((response) => {
              this.activeVueGenres = response.data;
            })
            .catch(() => {
              feedbackError(text.feedbackError);
            });
        }, 150);
      },
      submitDataFollowedArtists: function () {
        let artistsActive = [];
        this.vueArtists.forEach(function(artist) {
          if (artist.active) {
            artistsActive.push(artist);
          }
        });
        showLoader();
        axios
          .post(this.url, {
            artists: artistsActive,
            nbTracks: $('#nbTracks').val(),
            playlistName: this.playlistName
          })
          .then(function(response) {
            if (response.data.success) {
              feedbackSuccess(text.playlistSaveSucessFeedback);
            } else {
              feedbackError(text.feedbackError);
            }
          })
          .catch(function() {
            feedbackError(text.feedbackError);
          })
          .then(function() {
            hideLoader();
          });
      },
      addSelectedGenres: function(genre) {
        this.selectedGenres.push(genre);
        this.refreshVueArtists();
      },
      deleteSelectedGenre: function(genre) {
        this.selectedGenres.remove(genre);
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
        if (this.vueArtists.length == 0) {
          return;
        }
        
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
            if (app.selectedGenres.map(value => value.name).includes(genre)) {
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
