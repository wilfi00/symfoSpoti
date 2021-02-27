<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use \App\Entity\Genre;

class GenreManager extends AbstractManager
{
    protected $search;
    protected $regex;
    
    /**
     * FacturationDetailsManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param FacturationManager $facturationManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityClassName = Genre::class;
        parent::__construct($entityManager);
    }
    
    public function findAllBySearch(string $search)
    {
        
        $this->search = strtolower(trim($search));
        $resultGenres = [];
        $genres = $this->findAllInArray();
      
        // Si pas de recherche, on renvoit tout
        if (empty($search)) {
            return $genres;
        }
      
        // Recherche exact
        $genres0 = array_filter($genres, function($genre) {
            return $this->search == $genre['name'];
        });
      
        // Recherche exact (uk metalcore matchera uk metalcore)
        $genres1 = array_filter($genres, function($genre) {
            return preg_match('/\\b(\\w*' .  $this->search . '\\w*)\\b/', $genre['name']);
        });
        
        // Recherche inversée exact (exemple, uk metalcore matchera metalcore uk)
        $genres2 = array_filter($genres, function($genre) {
            return preg_match('/\\b(\\w*' . implode(' ', array_reverse(explode(' ', $this->search))) . '\\w*)\\b/', $genre['name']);
        });
		
	    // Recherche très générale en mode OU (uk metalcore renverra tous les uk et tous les metalcore)
		$regex = '/';
		$words = explode(' ', $this->search);
		foreach ($words as $word) {
		    $regex .= '\\b(\\w*' . $word . '\\w*)\\b|';
		}
		$this->regex = substr($regex, 0, -1) . '/'; // Supression du dernier caractère de la chaine pour enlever le ou |
		$genres3 = array_filter($genres, function($genre) {
		    return preg_match($this->regex, $genre['name']); 
		});
		
        return array_unique(array_merge_recursive($genres0, $genres1, $genres2, $genres3), SORT_REGULAR);
        
        /*
        var genres0 = genres.filter(genre => genre.name == input.val());
				
		// Recherche exact (uk metalcore matchera uk metalcore)
		var regex = '';
		regex +=  '\\b(\\w*' +  $.trim(input.val()) + '\\w*)\\b';
		var genres1 = genres.filter(genre => genre.name.search(regex) >= 0);

		// Recherche inversée exact (exemple, uk metalcore matchera metalcore uk)
		var regex = '';
		regex +=  '\\b(\\w*' +  $.trim(input.val().split(' ').reverse().join(' ')) + '\\w*)\\b';
		var genres2 = genres.filter(genre => genre.name.search(regex) >= 0);

		// Recherche très générale en mode OU (uk metalcore renverra tous les uk et tous les metalcore)
		var regex = '';
		input.val().split(' ').forEach(function(value) {
		   regex += '\\b(\\w*' + value + '\\w*)\\b|';
		});
		// Supression du dernier caractère de la chaine pour enlever le ou |
		regex = regex.substring(0, regex.length - 1);
		var genres3 = genres.filter(genre => genre.name.search(regex) >= 0);

		// On concatène tout et on enlève les genres dupliqués
		app.activeVueGenres = genres0.concat(genres1).concat(genres2).concat(genres3).unique();
		addEventToGenre(callbackAddGenre, callbackDeleteGenre);
		*/
    }
}
