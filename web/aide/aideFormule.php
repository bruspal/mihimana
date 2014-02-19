<!DOCTYPE html>
<html>
  <head>
    <title>Aide des formules</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/main.css" />
  </head>
  <body>
    <h1>Interface (lorsqu'on est dans la fenetre de test)</h1>
    <ul>
      <li>Formule: la formule a tester</li>
      <li>
        Variables: pour definir des variables.<br />
        c'est au format d'une variable par ligne definie par nom_variables:valeur.<br />
        Ainsi:<br />
        <ul>
          <li>a:123</li>
          <li>b:toto</li>
        </ul>
       Va créer les variable $a et $b utilisable dans les formules.
      </li>
      <li>
        Interprete: a 'non' les variables sont vu comme des types simples (nombre, chaine ou date). A 'oui' les varibales sont vu comme des expressions et sont resolue.<br />
        <strong>Attention :</strong>dans le cas de variables interprétées (Interprete = 'oui') les variables doivent etre des expressions valide<br />
        Par exemple si on definie les variables
        <ul>
          <li>a:toto</li>
          <li>b:10-10-2010</li>
          <li>c:12</li>
          <li>d:int((date()+30)+0.02)</li>
        </ul>
        en mode non interpreté on obtiendra les variables $a de type chaine, $b de type date,$c de type nombre et $d la chaine "int((date()+30)+0.02)"<br />
        en mode interpreté on obtiendra $a qui provoque une erreur ce n'est pas une expression valide, $b un nombre valant 10 - 10 - 2010 soit -2010, $c le nombre 12 et $d le resultat de l'expression.
        Dans ce cas il faut transformer les déclarations comme ceci:
        <ul>
          <li>a:"toto" (une expression ne contenant que la chaine "toto")</li>
          <li>b:"10-10-2010" (une date au bon format)</li>
          <li>c:12 (un nombre)</li>
          <li>d:int((date()+30)+0.02)</li>
        </ul>
      </li>
      <li>Debug: affiche les infos de debug</li>
    </ul>
    <h1>Types</h1>
    <ul>
      <li>Chaine: elles sont délimité par ". si on veux inclure un " dans la chaine il faut l'échapper par \". De meme pour un \ il faut utiliser \\</li>
      <li>nombre: 999 999.999 et .999 sont acceptés</li>
      <li>date: au format "JJ-MM-AAAA"</li>
      <li>variables: au format $nom. Le type est determiné automatiquement lorsque interprete est a non</li>
      <li>formule: au format [nom_formule]</li>
    </ul>
    <h1>Controles</h1>
    <ul>
      <li>si(condition?vrai:faux) : si condition est vrai execute vrai sinon faux</li>
    </ul>
    <h1>Operateurs</h1>
    <ul>
      <li>=, <>, <, <=, >, >=: operateurs de comparaison</li>
      <li>ET, OU : operateurs logique</li>
    </ul>
    <h1>Arithmetique</h1>
    <ul>
      <li> A+B : addition des nombres si A et B sont des nombre, si A ou B est une chaine effectue une concat, si A est une date et B un nombre additionne B jours a A</li>
      <li> A-B : soustractionne deux nombre, ou retire B jours a A si A est une date</li>
      <li> A*B : multipli A et B </li>
      <li> A/B : divise A par B </li>
      <li>( et ): priorise l'expression entre parenthéses.</li>
    </ul>
    <h1>Constantes</h1>
    <ul>
      <li>JOUR: nombre de jour, la date pivot est le 01/01/1970</li>
      <li>VRAI: 1</li>
      <li>FAUX: 0</li>
    </ul>
    <h1>Fonctions</h1>
    <ul>
      <li>int($valeur): retourne la $valeur tronqué a l'entier [int(67.89) => 67]</li>
      <li>chaine($nombre): converti un nombre en chaine</li>
      <li> fonction de test, concat $p1, $p2, $3 : func($p1,$p2,$p3)</li>
      <li>rd($decimal, $valeur): retourne la valeur arrondis au $decimal inferieur [rd(10, 28) => 20]</li>
      <li>rd($decimal, $valeur): retourne la valeur arrondis au $decimal supérieur [ru(10, 28) => 30]</li>
      <li>min($val1, ..., $valN): retourne la plus petite valeur parmis les parametres [min(1,2,3) => 1]</li>
      <li>max($val1, ..., $valN): retourne la plus grande valeur parmis les parametres [min(1,2,3) => 3]</li>
      <li>table("nom dans la table", "cle de recherche"[, date?]): retourne la valeur identifié par clé de recherche dans la table de parametre 'nom de table'</li>
      <li>formate ("format style PHP", variable1, ..., variable N): formate les variables, retourne une chaine;</li>
      <li>date(nbJour): retourne une date litteral a partir de l'entier nbJour, si nbJour est omis renvois la date du jour</li>
      <li>nbJour(date): retourne le nombre de jour entre la date date et le 01/01/1970, si date est omis retourne le nombre de jour depuis le 01/01/1970 et aujourd'hui.</li>
      <li>dateMoins(date1, date2): effectue la soustraction de jour entre date1 et date2, date 2 peux etre une date ou un entier</li>
      <li>datePlus(date1, date2): effectue l'addition de jour entre date1 et date2, date 2 peux etre une date ou un entier</li>
      <li>jour($date): retourne un nombre representant le jour de la date, $date peux etre une date ou un nombre</li>
      <li>mois($date): retourne un nombre representant le mois de la date, $date peux etre une date ou un nombre</li>
      <li>an($date, $decimal): retourne un nombre representant le mois de la date, $date peux etre une date ou un nombre. $decimal peux valoir 2 ou 4 pour choisir un resultat sur 2 ou 4 digits</li>
    </ul>
    <h1>Remarques</h1>
    <ul>
      <li>
        Les dates converties en entiers on leur date pivot au 01/01/1970. Ainsi la convertion date(1) generera la date 01-01-1970.<br />
        Nbr jour peux etre negatif ou nul dans ce cas on obtiens une date antérieure au 01-01-1970.<br />
        par exemple date(0) retournera 31-12-1969 et date(-1) retournera 30-12-1969.
      </li>
      <li>
        Il n'y a pas de type booleen, seulement FAUX equivaut a 0 ou une chaine vide et VRAI equivaut a tout ce qui n'est pas FAUX.
      </li>
      <li>
        Variables (a améliorer): pour inclure des variables de type chaine dans une chaine il faut utiliser la concatenation. si $a = "toto" alors "$a est content" provoquera une erreur. il faut ecrire $a+" est content"
      </li>
    </ul>
  </body>
</html>