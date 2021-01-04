<!DOCTYPE html>
    <?php
    include'index.php';
    
    //Obtengo la busqueda
    $search = $_GET['search'];
    //Remplazo los espacios por signos de + para poder usar la consulta en la genracion de la url con datamuse
    $search = str_replace(" ","+",$search);
    //Genero la url definiendo que el resultado sea ml (los resultados tengan un significado relacionado con este valor de cadena)
    //y el maximo de valores esperados sea 10 :&max=10
    $url = "api.datamuse.com/words?ml=".$search.".&max=10";
    // Crea un nuevo recurso cURL
    $ch = curl_init();
    // Establece la URL y otras opciones apropiadas
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    //ejecuta la sesión cURL
    $response = curl_exec($ch);

    $result = json_decode($response);
    $palabras = [];
    $PLOSarticles=[];
    $wikiarticles=[];
    $mergedArticles = [];

    for ($i=0; $i < count($result) ; $i++) {
      array_push($palabras,$result[$i]->word);
    }

    $url = "http://api.plos.org/search?q=title:".$palabras[0]."&start=0&rows=10";
    $PLOSClient = curl_init($url);
    curl_setopt($PLOSClient,CURLOPT_RETURNTRANSFER,true);
    $response = curl_exec($PLOSClient);

    $result = json_decode($response);
    $docs = $result->response->docs;
    if(is_array($docs)){
    for ($i=0; $i < count($docs); $i++) {
      $results = new stdClass();
      $results->title = $docs[$i]->title_display;
      $results->abstract = $docs[$i]->abstract[0];
      $results->score = $docs[$i]->score;
      $results->origin = "PLOS";

      array_push($PLOSarticles,$results);
    }
  }
    $maxPLOS = 0;

    for ($i=0; $i < count($PLOSarticles); $i++) {
      if ($PLOSarticles[$i]->score > $maxPLOS) {
        $maxPLOS = $PLOSarticles[$i]->score;
      }
    }

    for ($i=0; $i < count($PLOSarticles); $i++) {
      $PLOSarticles[$i]->normalizedScore = $PLOSarticles[$i]->score/$maxPLOS;
    }


    $url = "https://en.wikipedia.org/w/api.php?action=query&list=search&srsearch=".$query."&utf8=&format=json";
    $wikiClient = curl_init($url);
    curl_setopt($wikiClient,CURLOPT_RETURNTRANSFER,true);
    $response = curl_exec($wikiClient);

    $result = json_decode($response);
    $docs = $result->query->search;
   if(is_array($docs)){
    for ($i=0; $i <count($docs); $i++) {
      $results = new stdClass();
      $results->title = $docs[$i]->title;
      $results->abstract = $docs[$i]->snippet;
      $results->score = $docs[$i]->pageid;
      $results->origin = "Wikipedia";

      array_push($wikiarticles,$results);
    }
  }
    $maxWiki = 0;

    for ($i=0; $i < count($wikiarticles); $i++) {
      if ($wikiarticles[$i]->score > $maxWiki) {
        $maxWiki = $wikiarticles[$i]->score;
      }
    }

    for ($i=0; $i < count($wikiarticles); $i++) {
      $wikiarticles[$i]->normalizedScore = $wikiarticles[$i]->score/$maxWiki;
    }

    $mergedArticles = array_merge($PLOSarticles, $wikiarticles);

    $n = count($mergedArticles);
    for($i = 0; $i < $n; $i++)
    {
        for ($j = 0; $j < $n - $i - 1; $j++)
        {
            if ($mergedArticles[$j]->normalizedScore < $mergedArticles[$j+1]->normalizedScore)
            {
                $t = $mergedArticles[$j];
                $mergedArticles[$j] = $mergedArticles[$j+1];
                $mergedArticles[$j+1] = $t;
            }
        }
    }

    for ($i=0; $i < count($mergedArticles) ; $i++) {
      $url='https://en.wikipedia.org/wiki/'.$mergedArticles[$i]->title;

      echo "<div class='result-item'>";
      echo '<a class="item-hyper" href='.str_replace(' ', '%20', $url).">";
      echo "<p class='item-snippet'>".$mergedArticles[$i]->abstract."</p>";
      echo "</a>";
      echo "<p class='item-url'> Original score: ".$mergedArticles[$i]->score."        Normalized score: ". $mergedArticles[$i]->normalizedScore ."</p>";
      echo "<p class='item-title'>".$mergedArticles[$i]->title."</p>";
      echo "</div>";
    }
  ?>
