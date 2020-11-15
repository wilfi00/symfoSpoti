import Vue from 'vue


setTimeout(function() { 
  Vue.component('genre-item', {
  	props: {
  	  genre: Object,
  	},
  	template: `<li class="genre" v-bind:id="'genre' + genre.id" v-bind:data-id="genre.id" style="">{{ genre.name }}</li>`,
  })
  
  
  app = new Vue({
    el: '#appVueGenre',
    delimiters: ['${', '}'],
    data: {
      vueArtists: vueArtists,
      vueGenres: vueGenres,
      activeVueGenres: vueGenres,
      saveVueArtists: this.vueArtists,
      genres: genres,
      selectedGenres: [],
      unwantedGenres: [],
      url: url,
      playlistName: '',
      active: true,
      nbActiveArtists: this.vueArtists.length,
      text: [],
    },
    methods: {
      submitData: function () {
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
            hideLoader();
            if (response.data.success) {
              feedbackSuccess(text.playlistSaveSucessFeedback);
            } else {
              feedbackError(text.feedbackError);
            }
          });
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
