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
    $curlSession = curl_exec($ch);
    //Obtenemos todas las palabras,score y tag relacionadas de datamuse
    $result = json_decode($curlSession );
    $firstWord = [];
    array_push($firstWord,$result[0]->word);
    //var_dump($result);


    /**PLOS */
    
    $url = "http://api.plos.org/search?q=title:".$firstWord[0]."&start=0&rows=10";
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
    $curlSession = curl_exec($ch);
    $result = json_decode($curlSession); 
    $allResources = $result->response->docs;
    $plosResources=[];
    
    $maxPlosScore=0;
    if(is_array($allResources)){
      for ($i=0; $i < count($allResources); $i++) {
        $resource = new stdClass();
        $resource->journal = $allResources[$i]->journal;
        $resource->title = $allResources[$i]->title_display;
        $resource->score = $allResources[$i]->score;
        $resource->article_type = $allResources[$i]->article_type;
        $resource->reference = 'plos';
        $resource->normalizedScore = 0;
        array_push($plosResources,$resource);
        if($maxPlosScore<=$plosResources[$i]->score) {
          $maxPlosScore = $plosResources[$i]->score;
        }

      }
      //Obtenemos normalized Score
      for ($i=0; $i < count($plosResources); $i++) {
        $resourceScore=$plosResources[$i]->score;
        $plosResources[$i]->normalizedScore = $resourceScore/$maxPlosScore;
      }
    }


   /**EUROPEANA */
   $url = "https://api.europeana.eu/record/v2/search.json?wskey=NkPJPNRQj&query=". $firstWord[0]."&start=0&rows=10";
   $ch = curl_init();
   curl_setopt($ch,CURLOPT_URL, $url);
   curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
   $curlSession = curl_exec($ch);
   $result = json_decode($curlSession);
   $allResources = $result->response->items;
   $maxEuropeanaScore=0;
   $europeanaResources=[];
   if(is_array($allResources)){
    for ($i=0; $i < count($allResources); $i++) {
    $resource = new stdClass();
    $resource->title = $allResources[$i]->title;
    $resource->guid = $allResources[$i]->guid;
    $resource->score = $allResources[$i]->score;
    $resource->type = $allResources[$i]->type;
    $resource->year = $allResources[$i]->year;
    $resource->reference = 'europeana';
    $resource->normalizedScore = 0;
    array_push($europeanaResources,$resource);
    if($maxEuropeanaScore<=$europeanaResources[$i]->score) {
      $maxEuropeanaScore = $europeanaResources[$i]->score;
    }

    }
    //Obtenemos normalized Score
    for ($i=0; $i < count($europeanaResources); $i++) {
      $resourceScore=$europeanaResources[$i]->score;
      $europeanaResources[$i]->normalizedScore = $resourceScore/$maxEuropeanaScore;
    }
   }

  /**JUNTAMOS TODOS LOS RECURSOS OBTENIDOS*/
   global $plosResources, $europeanaResources;
   $allResourcesForQuery = array_merge($plosResources, $europeanaResources);
  if(is_array($allResourcesForQuery)){
    $totalR = count($allResourcesForQuery);
    global $totalR;
    for($i = 0; $i < $totalR ; $i++){
      for ($j = 0; $j < $totalR  - $i - 1; $j++){
        //Ordenamos por metodo burbuja
          if ($allResourcesForQuery[$j]->normalizedScore < $allResourcesForQuery[$j+1]->normalizedScore){
              $aux = $allResourcesForQuery[$j];
              $allResourcesForQuery[$j] = $allResourcesForQuery[$j+1];
              $allResourcesForQuery[$j+1] = $aux;
          }
      }
    }

    for ($i=0; $i < count($allResourcesForQuery) ; $i++) {

      if($allResourcesForQuery[$i]->reference==='europeana'){
        echo '<div class="container" style="margin: 50px;">';
        echo '<a class="item-hyper" href='.$allResourcesForQuery[$i]->guid.'>';
        echo "</a>";
        echo "<p class='item-title'> Title:".$allResourcesForQuery[$i]->title."</p>";
        echo "<p class='item-title'>Normalized Score:".$allResourcesForQuery[$i]->normalizedScore."</p>";
        echo "<p class='item-title'>Score:".$allResourcesForQuery[$i]->score."</p>";
        echo "<p class='item-title'>Resource:".$allResourcesForQuery[$i]->reference."</p>";
        echo "</div>";
      }

      if($allResourcesForQuery[$i]->reference ==='plos'){
        echo '<div class="container" style="margin: 50px;">';
        echo "<p class='item-title'>Title:".$allResourcesForQuery[$i]->title."</p>";
        echo "<p class='item-title'>Normalized Score:".$allResourcesForQuery[$i]->normalizedScore."</p>";
        echo "<p class='item-title'>Score:".$allResourcesForQuery[$i]->score."</p>";
        echo "<p class='item-title'>Journal:".$allResourcesForQuery[$i]->journal."</p>";
        echo "<p class='item-title'>Resource:".$allResourcesForQuery[$i]->reference."</p>";
        echo "<p class='item-title'>Artycle type:".$allResourcesForQuery[$i]->article_type."</p>";
        echo "</div>";

      }

    }
  };
  ?>
