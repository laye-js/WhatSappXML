<?php
// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Vérifier si le formulaire est pour l'ajout d'un contact ou la création d'un groupe
  if (isset($_POST['ajouter_contact'])) {
    // Récupérer les données du formulaire d'ajout de contact
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $numero = $_POST['numero'];
    $photo = $_POST['photo'];
    $status = $_POST['status'];

    // Charger le fichier XML
    $xml = simplexml_load_file('whatsapp.xml');

    // Générer un nouvel identifiant pour le contact
    $contactId = count($xml->discussions->contacts->contact) + 1;

    // Créer un nouvel élément contact
    $contact = $xml->discussions->contacts->addChild('contact');
    $contact->addAttribute('id', $contactId);
    $contact->addChild('nom', $nom);
    $contact->addChild('prenom', $prenom);
    $contact->addChild('numero_telephone', $numero);
    $contact->addChild('photo_profile', $photo);
    $contact->addChild('status', $status);
    $contact->addChild('messages');

    // Sauvegarder les modifications dans le fichier XML
    $xml->asXML('whatsapp.xml');
  } elseif (isset($_POST['creer_groupe'])) {
    // Récupérer les données du formulaire de création de groupe
    $nomGroupe = $_POST['nom_groupe'];
    $adminId = $_POST['admin_id'];
    $membres = $_POST['membres'];

    // Charger le fichier XML
    $xml = simplexml_load_file('whatsapp.xml');

    // Générer un nouvel identifiant pour le groupe
    $groupeId = count($xml->discussions->groupes->groupe) + 1;

    // Créer un nouvel élément groupe
    $groupe = $xml->discussions->groupes->addChild('groupe');
    $groupe->addAttribute('id', $groupeId);
    $groupe->addChild('nom_groupe', $nomGroupe);
   

    // Ajouter les membres au groupe
    $membresElement = $groupe->addChild('membres');
    foreach ($membres as $membreId) {
      $contact = $xml->xpath("//contact[@id='$membreId']")[0];
      $membreElement = $membresElement->addChild('contact');
      $membreElement->addAttribute('id', $membreId);
      $membreElement->addChild('nom', (string)$contact->nom_contact);
      $membreElement->addChild('prenom', (string)$contact->prenom);
      $membreElement->addChild('numero_telephone', (string)$contact->numero_telephone);
      $membreElement->addChild('photo_profile', (string)$contact->photo_profile);
      $membreElement->addChild('status', (string)$contact->status);
    }
    $groupe->addChild('admin')->addAttribute('ref', $adminId);
    $groupe->addChild('messages');
    // Sauvegarder les modifications dans le fichier XML
    $xml->asXML('whatsapp.xml');
  }

}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WhatsApp</title>
  <!-- Ajouter le lien vers le fichier CSS de Bootstrap -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
  <style>
    /* Ajouter du style pour l'apparence du chat */
    .chat-container {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      background-color: #f7f7f7;
    }

    .contact-card {
      margin-bottom: 20px;
      padding: 10px;
      background-color: #fff;
      border-radius: 5px;
    }

    .contact-card .card-title {
      margin-bottom: 10px;
    }

    .message {
      margin-top: 10px;
      padding: 10px;
      background-color: #e5e5ea;
      border-radius: 5px;
    }

    .message p {
      margin-bottom: 5px;
    }

    .message.sender {
      background-color: #dcf8c6;
    }

    .message.receiver {
      background-color: #fff;
    }

    .message-info {
      font-size: 12px;
      color: #777;
    }

    .form-container {
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container chat-container">

    <!-- Affichage des contacts -->
    <h2>Contacts</h2>
    <?php
    // Charger le fichier XML
    $xml = simplexml_load_file('whatsapp.xml');

    // Parcourir chaque contact
    foreach ($xml->discussions->contacts->contact as $contact) {
      echo '<div class="card contact-card">';
      echo '<div class="card-body">';
      echo '<h5 class="card-title">' . $contact->prenom . ' ' . $contact->nom_contact . '</h5>';
      echo '<p class="card-text">' . $contact->numero_telephone . '</p>';
      echo '<p class="card-text">' . $contact->status . '</p>';
      echo '</div>';
      echo '</div>';
    }
    ?>

    <!-- Formulaire d'ajout de contact -->
    <div class="form-container">
      <h2>Ajouter un contact</h2>
      <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <div class="form-group">
          <label for="prenom">Prénom :</label>
          <input type="text" name="prenom" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="nom">Nom :</label>
          <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="numero">Numéro de téléphone :</label>
          <input type="text" name="numero" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="photo">URL de la photo de profil :</label>
          <input type="text" name="photo" class="form-control">
        </div>
        <div class="form-group">
          <label for="status">Statut :</label>
          <input type="text" name="status" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary" name="ajouter_contact">Ajouter</button>
      </form>
    </div>

   <!-- Formulaire de création de groupe -->
   <div class="form-container">
    <h2>Créer un groupe</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
      <div class="form-group">
        <label for="nom_groupe">Nom du groupe :</label>
        <input type="text" name="nom_groupe" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="admin_id">Administrateur :</label>
        <select name="admin_id" class="form-control" required>
          <?php
          // Parcourir chaque contact pour les afficher comme options
          foreach ($xml->discussions->contacts->contact as $contact) {
            echo '<option value="' . $contact['id'] . '">' . $contact->prenom . ' ' . $contact->nom_contact . '</option>';
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label for="membres">Membres :</label>
        <select name="membres[]" class="form-control" multiple required>
          <?php
          // Parcourir chaque contact pour les afficher comme options
          foreach ($xml->discussions->contacts->contact as $contact) {
            echo '<option value="' . $contact['id'] . '">' . $contact->prenom . ' ' . $contact->nom_contact . '</option>';
          }
          ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" name="creer_groupe">Créer</button>
    </form>
  </div>
  </div>


  <!-- Ajouter le lien vers le fichier JavaScript de Bootstrap -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
