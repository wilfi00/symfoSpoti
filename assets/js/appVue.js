import Vue from 'vue';
import axios from "axios";

$( document ).ready(function() {
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
  
  Vue.component('artist-item', {
  	props: {
  	  artist: Object,
  	},
  	template: `
  	<div class="artistBloc artistFollowedBloc">
    	<div class="popover-content d-none"><ul class="list-group list-group-flush"><li class="list-group-item" v-for='(genre, index) in artist.genres' :key='index'>{{ genre }}</li></ul></div>
      <svg title="Genres de l'artiste" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="picto-info bi bi-info-circle-fill" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM8 5.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
      </svg>
  	  <img class="image" :class="{ 'disabled' : artist.active == false}" v-bind:src="artist.images[0].url" />
  	  <br>
	    <p><div class="checkbox-artist custom-control custom-checkbox"><input @click="artist.active ? artist.active = false :artist.active = true" :checked=artist.active type="checkbox" class="custom-control-input" :id=artist.id>  <label class="custom-control-label" :for=artist.id><span class="artistLabel" :class="{ 'disabled' : artist.active == false}">{{ artist.name }}</span></label></div></p>
  	  </div>`,
  })
  
  window.app = new Vue({
    el: '#app',
    delimiters: ['${', '}'],
    data: {
      vueArtists: vueArtists,
      vueGenres: vueGenres,
      selectedGenres: [],
      unwantedGenres: [],
      url: url,
      urlSearchGenre: urlSearchGenre,
      playlistName: '',
      active: true,
      inputSearchGenre: '',
      timer: '',
      checkAllArtistsIndeterminate: false,
    },
    directives: {
      indeterminate: function(el, binding) {
       el.indeterminate = Boolean(binding.value)
      }
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
              this.vueGenres = response.data;
              this.updateActiveGenres();
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
        genre.active = false;
        this.selectedGenres.push(genre);
        this.checkAllArtistsIndeterminate = true;
        this.refreshVueArtists();
      },
      deleteSelectedGenre: function(genre) {
        genre.active = true;
        this.selectedGenres.remove(genre);
        this.refreshVueArtists();
      },
      addUnwantedGenres: function(event) {
        this.unwantedGenres.push(event.srcElement.dataset.name);
        this.refreshVueArtists();
      },
      // Rafraichis les artistes en fonction des filtres
      refreshVueArtists: function(event) {
        if (this.vueArtists.length > 0) {
          this.refreshVueArtistsByGenres(document.getElementById('checkAll').checked);
        }
        //this.vueArtists = this.refreshVueArtistsByUnwantedGenres(this.refreshVueArtistsByGenres());
      },
      // Ressort les artistes qui ont les genres sélectionnés
      refreshVueArtistsByGenres: function(checkAll) {
        if (this.vueArtists.length == 0) {
          return;
        }
        
        // Active à true pour les genres sélectionnés
        this.vueArtists.forEach(function(artist) {
          if ((checkAll && !app.checkAllArtistsIndeterminate) || (app.selectedGenres.length <= 0 && checkAll)) {
            artist.active = true;
            app.checkAllArtistsIndeterminate = true;
            return;
          } else if (!checkAll) {
            artist.active = false;
            return;
          }
          
          artist.active = false;
          for (const genre of artist.genres) {
            if (app.selectedGenres.map(value => value.name).includes(genre)) {
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
      },
      getActiveArtists: function() {
        return this.vueArtists.filter(artist => artist.active);
      },
      getIdActiveArtists: function() {
        return array_column(this.getActiveArtists(), 'id');
      },
      getNbActiveArtists: function() {
        return this.getActiveArtists().length;
      },
      getActiveGenres: function() {
        return this.vueGenres.filter(genre => genre.active);
      },
      updateActiveGenres: function() {
        this.vueGenres.forEach(function(genre) {
          genre.active = app.selectedGenres.filter(selectedGenre => selectedGenre.id === genre.id).length === 0;
        });
      },
    } 
  })
});
