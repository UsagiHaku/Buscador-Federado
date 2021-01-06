# Buscador-Federado
Buscador federado que integra los resultados de 2 buscadores existentes (Europeana y PLOS) . 
El buscador federado tiene una expansión de consultas utilizando dataMuse.

La aplicación Web permitirá al usuario realizar una consulta conjuntiva (AND) a partir de los términos proporcionados en una caja de texto
(no habrán operadores ni funciones). Los términos serán expandidos y consultados en los dos motores. Los resultados serán integrados y ordenados a partir 
de la normalización del valor de posicionamiento proporcionado por cada buscador.

Se presentarán enlaces a los documentos recuperados, el buscador que lo recuperó, el valor original de su relevancia y el valor normalizado que fue utilizado 
para el ranking.

#### Imagen
![img1](https://github.com/UsagiHaku/Buscador-Federado/blob/main/Captura%20de%20Pantalla%202021-01-04%20a%20la(s)%2019.06.05.png)

## Instalación 🔧

#### Instalación de PHP en Mac usando la terminal (PHP 7.3)

```
curl -s https://php-osx.liip.ch/install.sh | bash -s 7.3
```
#### Instalación de PHP en Linux usando la terminal (PHP 5)

```
apt-get install php5-common libapache2-mod-php5 php5-cli
```

#### Más información sobre el proceso de instalación de php
https://www.geeksforgeeks.org/how-to-execute-php-code-using-command-line/  
https://www.php.net/manual/es/install.php


## Ejecución 🔧

Para correr el proyecto, usamos el siguiente comando:

```
php file_name.php

```
Podemos iniciar el servidor para probar el código php usando el siguiente comando:

```
php -S localhost:8080 
```

## Herrramientas 🛠️
<https://pro.europeana.eu/page/search>   
<http://api.plos.org/>  
https://www.mysqltutorial.org/basic-mysql-tutorial.aspx  
https://www.php.net/  
