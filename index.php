<?php 
$xml = simplexml_load_file('whatsapp.xml');

if (!empty($_POST['destinataire']) && !empty($_POST['contenu_message'])) {
        $destinataire = $_POST['destinataire'];
        $contenuMessage = $_POST['contenu_message'];

        // Trouver le contact correspondant
        $contactObj = $xml->xpath("//contact[numero_telephone='$destinataire']");

        // Vérifier si le contact existe
        if ($contactObj) {
            // Générer un nouvel identifiant pour le message
            $nouvelId = max($xml->xpath("//message/@id")) + 1;

            // Créer un nouvel élément de message
            $nouveauMessage = $contactObj[0]->messages->addChild('message');
            $nouveauMessage->addAttribute('id', $nouvelId);
            $nouveauMessage->addAttribute('type', 'texte');
            $nouveauMessage->addAttribute('expediteur', '1');

            // Ajouter le contenu du message
            $nouveauMessage->addChild('contenu', $contenuMessage);

            // Ajouter les informations sur le message
            $messageInfo = $nouveauMessage->addChild('message_info');
            $messageInfo->addAttribute('heure', date('Y-m-d\TH:i:s'));
            $messageInfo->addAttribute('statut', 'double check');

            // Enregistrer les modifications dans le fichier XML
            $xml->asXML('whatsapp.xml');

            // Afficher un message de succès
            echo "Le message a été envoyé à $destinataire avec le contenu : $contenuMessage";
        } else {
            // Afficher un message d'erreur si le contact n'existe pas
            echo "Le contact $destinataire n'existe pas.";
        }
} elseif (isset($_POST['destinataire']) || isset($_POST['contenu_message'])) {
        // Afficher un message d'erreur si les champs requis ne sont pas remplis
        echo "Veuillez remplir tous les champs du formulaire.";
    }
// Vérifier si les champs requis sont remplis pour l'envoi dans un groupe
if (!empty($_POST['groupe']) && !empty($_POST['contenu_message']) && isset($_POST['expediteur'])) {
        $groupe = $_POST['groupe'];
        $contenuMessage = $_POST['contenu_message'];
        $expediteurId = $_POST['expediteur'];

        // Trouver le groupe correspondant
        $groupeObj = $xml->xpath("//groupe[nom_groupe='$groupe']");

        // Vérifier si le groupe existe
        if ($groupeObj) {
            // Générer un nouvel identifiant pour le message
            $nouvelId = max($xml->xpath("//message/@id")) + 1;

            // Créer un nouvel élément de message
            $nouveauMessage = $groupeObj[0]->messages->addChild('message');
            $nouveauMessage->addAttribute('id', $nouvelId);
            $nouveauMessage->addAttribute('type', 'texte');
            $nouveauMessage->addAttribute('expediteur', $expediteurId);

            // Ajouter le contenu du message
            $nouveauMessage->addChild('contenu', $contenuMessage);

            // Ajouter les informations sur le message
            $messageInfo = $nouveauMessage->addChild('message_info');
            $messageInfo->addAttribute('heure', date('Y-m-d\TH:i:s'));
            $messageInfo->addAttribute('statut', 'double check');

            // Enregistrer les modifications dans le fichier XML
            $xml->asXML('whatsapp.xml');

            // Afficher un message de succès
            echo "Le message a été envoyé dans le groupe $groupe avec le contenu : $contenuMessage";
        } else {
            // Afficher un message d'erreur si le groupe n'existe pas
            echo "Le groupe $groupe n'existe pas.";
        }
    } elseif (isset($_POST['groupe']) || isset($_POST['contenu_message']) || isset($_POST['expediteur'])) {
        // Afficher un message d'erreur si les champs requis ne sont pas remplis
        echo "Veuillez remplir tous les champs du formulaire.";
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

    <!-- Affichage des messages pour chaque contact -->
    <h2>Contacts</h2>
    <?php
// Charger le fichier XML
$xml = simplexml_load_file('whatsapp.xml');

// Parcourir chaque contact
foreach ($xml->discussions->contacts->contact as $contact) {
    echo '<div class="card contact-card">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">' . $contact->prenom . ' ' . $contact->nom_contact . '</h5>';
    echo '<p class="card-text">Numéro de téléphone : ' . $contact->numero_telephone . '</p>';
    echo '<img src="' . $contact->photo_profile . '" alt="Photo de profil" class="img-thumbnail">';
    echo '<p class="card-text">Statut : ' . $contact->status . '</p>';
    echo '<h6>Messages :</h6>';

    foreach ($contact->messages->message as $message) {
        $expediteurId = (string)$message['expediteur'];
        $expediteur = $xml->xpath("//contact[@id='$expediteurId']");
        $expediteurNom = $expediteur[0]->prenom . ' ' . $expediteur[0]->nom_contact;

        echo '<div class="message';
        if ($expediteurId == 1) {
            echo ' sender">';
        } else {
            echo ' receiver">';
        }
        echo '<p>' . $message->contenu . '</p>';
        echo '<p class="message-info">' . $expediteurNom . ' - ' . $message->message_info['heure'] . '</p>';
        echo '</div>';
    }

    // Formulaire d'envoi de message
    echo '<form action="" method="post" class="form-container">';
    echo '<input type="hidden" name="destinataire" value="' . $contact->numero_telephone . '">';
    echo '<div class="form-group">';
    echo '<textarea class="form-control" id="contenu_message" name="contenu_message" rows="3" placeholder="Écrire un message..."></textarea>';
    echo '</div>';
    echo '<button type="submit" class="btn btn-primary">Envoyer</button>';
    echo '</form>';

    echo '</div>';
    echo '</div>';

  // Vérifier si les champs requis sont remplis pour l'envoi à un contact
    
}
?>

    <!-- Affichage des messages pour chaque groupe -->
    <h2>Groupes</h2>
    <?php
// Charger le fichier XML
$xml = simplexml_load_file('whatsapp.xml');

// Parcourir chaque groupe
foreach ($xml->discussions->groupes->groupe as $groupe) {
    echo '<div class="contact-card">';
    echo '<h2>' . $groupe->nom_groupe . '</h2>';

    // Afficher les membres du groupe
    echo '<h3>Membres du groupe:</h3>';
    echo '<ul>';
    foreach ($groupe->membres->contact as $membre) {
        echo '<li>' . $membre->prenom . ' ' . $membre->nom_contact . '</li>';
    }
    echo '</ul>';

    // Afficher les messages du groupe
    echo '<h3>Messages du groupe:</h3>';
    echo '<ul class="message-list">';
    foreach ($groupe->messages->message as $message) {
        $expediteurId = (string)$message['expediteur'];
        $expediteur = $groupe->xpath("//contact[@id='$expediteurId']");
        $expediteurNom = (string)$expediteur[0]->prenom . ' ' . (string)$expediteur[0]->nom_contact;
        $contenuMessage = (string)$message->contenu;
        $heureMessage = (string)$message->message_info['heure'];

        echo '<li class="message">';
        echo '<div class="message-header">';
        echo '<strong>' . $expediteurNom . '</strong>';
        echo '<span class="message-time">' . $heureMessage . '</span>';
        echo '</div>';
        echo '<div class="message-content">' . $contenuMessage . '</div>';
        echo '</li>';
    }
    echo '</ul>';

    // Formulaire d'envoi de message dans un groupe
    echo '<form action="" method="post">';
    echo '<input type="hidden" name="groupe" value="' . $groupe->nom_groupe . '">';
    echo '<div class="form-group">';
    echo '<select class="form-control" id="expediteur" name="expediteur">';
    foreach ($groupe->membres->contact as $membre) {
        echo '<option value="' . $membre['id'] . '">' . $membre->prenom . ' ' . $membre->nom_contact . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<textarea class="form-control" id="contenu_message" name="contenu_message" rows="3" placeholder="Écrire un message..."></textarea>';
    echo '</div>';
    echo '<button type="submit" class="btn btn-primary">Envoyer</button>';
    echo '</form>';

    

    echo '</div>';
}
?>

  </div>
  <!-- Ajouter le lien vers le fichier JavaScript de Bootstrap -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>