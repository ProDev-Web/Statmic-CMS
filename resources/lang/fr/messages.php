<?php

return [
    'activate_account_notification_body' => 'Vous recevez cet email car nous avons reçu une demande de réinitialisation du mot de passe pour votre compte.',
    'activate_account_notification_subject' => 'Activez votre compte',
    'addon_has_more_releases_beyond_license_body' => 'Vous pouvez mettre à jour, mais devrez mettre à niveau ou acheter une nouvelle licence.',
    'addon_has_more_releases_beyond_license_heading' => 'Cet addon a plus de versions au-delà de votre limite de licence.',
    'addon_list_loading_error' => 'Une erreur s’est produite lors du chargement des addons. Réessayez plus tard.',
    'asset_container_allow_uploads_instructions' => 'Si activé, donne la possibilité aux utilisateurs de téléverser des fichiers dans ce conteneur.',
    'asset_container_blueprint_instructions' => 'Les Blueprints définissent les champs personnalisés additionnels disponibles lors de la modification des ressources.',
    'asset_container_create_folder_instructions' => 'Si activé, donne la possibilité aux utilisateurs de créer des dossiers dans ce conteneur.',
    'asset_container_disk_instructions' => 'Les disques du système de fichiers vous permettent de préciser où seront stockés les fichiers, soit localement, soit sur un emplacement externe comme Amazon S3. Ils peuvent être configurés dans `config/filesystems.php`',
    'asset_container_handle_instructions' => 'Comment vous allez faire référence à ce conteneur sur le frontal. Ne peut pas être facilement changé.',
    'asset_container_intro' => 'Les fichiers media et documents sont stockés dans des répertoires sur votre serveur ou dans d’autres services de stockage de fichiers. Chacun de ces emplacements est appelé un conteneur.',
    'asset_container_move_instructions' => 'Si activé, donne la possibilité aux utilisateurs de déplacer les fichiers au sein de ce conteneur.',
    'asset_container_quick_download_instructions' => 'Si activé, ajoute un bouton de téléchargement rapide dans le Gestionnaire de Ressources.',
    'asset_container_rename_instructions' => 'Si activé, donne la possibilité aux utilisateurs de renommer les fichiers dans ce conteneur.',
    'asset_container_title_instructions' => 'Habituellement un nom au pluriel, comme "Images" ou "Documents".',
    'asset_folders_directory_instructions' => 'Nous vous recommandons d’éviter les espaces et les caractères spéciaux pour garder vos URL propres.',
    'blueprints_intro' => 'Les Blueprints définissent et organisent les champs afin de créer les modèles de contenu pour les collections, les formulaires et d’autres types de données.',
    'blueprints_title_instructions' => 'Habituellement un nom singulier, comme "Article" ou "Produit"',
    'cache_utility_application_cache_description' => 'Le cache unifié de Laravel utilisé par Statamic, les extensions tierces et les packages Composer.',
    'cache_utility_description' => 'Gérez et affichez des informations importantes sur les différentes couches de mise en cache de Statamic.',
    'cache_utility_image_cache_description' => 'Le cache d’images stocke des copies de toutes les images transformées et redimensionnées.',
    'cache_utility_stache_description' => 'Le Stache est le magasin de contenu de Statamic qui fonctionne de manière similaire à une base de données. Il est généré automatiquement à partir de vos fichiers de contenu.',
    'cache_utility_static_cache_description' => 'Les pages statiques contournent complètement Statamic et sont rendues directement à partir de votre serveur pour des performances optimales.',
    'collection_configure_date_behavior_private' => 'Privé - Non listée, URLs 404',
    'collection_configure_date_behavior_public' => 'Public - Toujours visible',
    'collection_configure_date_behavior_unlisted' => 'Non listé - URLs accessibles',
    'collection_configure_dated_instructions' => 'Les dates de publication peuvent être utilisées pour planifier la publication/dépublication du contenu.',
    'collection_configure_handle_instructions' => 'Comment vous allez faire référence à cette collection sur le frontal. Ne peut pas être facilement changé.',
    'collection_configure_intro' => 'Une collection est un groupe d’entrées liées qui partagent comportement, attributs et paramètres.',
    'collection_configure_layout_instructions' => 'Définissez la disposition par défaut de cette collection. Les entrées peuvent outrepasser ce paramètre avec un champ `template` nommé `layout`. La modification de ce paramètre est inhabituelle.',
    'collection_configure_template_instructions' => 'Définissez le modèle par défaut de cette collection. Les entrées peuvent outrepasser ce paramètre avec un champ `template`.',
    'collection_configure_title_instructions' => 'Nous recommandons un nom au pluriel, comme "Articles" ou "Produits".',
    'collection_next_steps_configure_description' => 'Configurer les URLs et les routes, définissez les Blueprints, les comportements des dates, l’ordonnancement et d’autres options.',
    'collection_next_steps_create_entry_description' => 'Créez votre première entrée ou écrasez une poignée d’entrées génériques, à vous de jouer.',
    'collection_next_steps_documentation_description' => 'Apprenez-en plus au sujet des collections, comment elles fonctionnent et comment les configurer.',
    'collection_next_steps_scaffold_description' => 'Générez rapidement des Blueprints vides et des vues pour le frontal basées sur le nom de votre collection.',
    'collection_scaffold_instructions' => 'Choisissez les ressources vides à générer. Les fichiers existants ne seront pas écrasés.',
    'collections_amp_instructions' => 'Activez les Pages Accélérées pour Mobile (AMP). Ajoutez automatiquement des routes et des URLs pour les entrées de cette collection. Apprenez-en plus dans la [documentation](https://statamic.dev/amp)',
    'collections_blueprint_instructions' => 'Les entrées de cette collection peuvent utiliser n’importe lequel de ces Blueprints.',
    'collections_default_publish_state_instructions' => 'Lors de la création de nouvelles entrées dans cette collection, l’indicateur de publication basculera par défaut sur **true** plutôt que sur **false** (brouillon).',
    'collections_future_date_behavior_instructions' => 'Comment les entrées datées dans le futur doivent se comporter.',
    'collections_links_instructions' => 'Les entrées de cette collection peuvent contenir des liens (redirections) vers d’autres entrées ou URLs.',
    'collections_mount_instructions' => 'Choisissez une entrée sur laquelle cette collection doit être montée. En savoir plus dans la [documentation](https://statamic.dev/collections-and-entries#mounting).',
    'collections_orderable_instructions' => 'Activez le réordonnancement manuel par glisser-déposer.',
    'collections_past_date_behavior_instructions' => 'Comment les entrées datées ayant expiré doivent se comporter.',
    'collections_route_instructions' => 'La route contrôle le modèle d’URL des entrées.',
    'collections_sort_direction_instructions' => 'Le sens de tri par défaut.',
    'collections_taxonomies_instructions' => 'Reliez les entrées de cette collection à des taxonomies. Des champs seront automatiquement ajoutés pour publier des formulaires.',
    'email_utility_configuration_description' => 'Les paramètres de messagerie sont configurés dans <code>:path</code>',
    'email_utility_description' => 'Vérifiez la configuration du courrier électronique et envoyez un test.',
    'expect_root_instructions' => 'Considérer la première page de l’arborescence comme une page "racine" ou "d’accueil".',
    'field_conditions_instructions' => 'Quand afficher ou masquer ce champ.',
    'field_desynced_from_origin' => 'Désynchronisé de l’origine. Cliquez pour synchroniser et revenir à la valeur d’origine.',
    'field_synced_with_origin' => 'Synchronisé avec l’origine. Cliquez ou modifiez le champ à désynchroniser.',
    'field_validation_advanced_instructions' => 'Ajouter des règles de validation plus avancées à ce champ.',
    'field_validation_required_instructions' => 'Permet de contrôler si ce champ est obligatoire ou non.',
    'fields_blueprints_description' => 'Les Blueprints vous permettent de mélanger et d’associer des champs et des jeux de champs pour créer les structures de contenu des collections et d’autres types de données.',
    'fields_display_instructions' => 'L’étiquette du champ affichée dans le Panneau de configuration.',
    'fields_fieldsets_description' => 'Les jeux de champs sont des groupements simples, flexibles et totalement facultatifs de champs qui aident à organiser les champs réutilisables et préconfigurés.',
    'fields_handle_instructions' => 'La variable de modèle du champ.',
    'fields_instructions_instructions' => 'Texte affiché sous l’étiquette du champ. Markdown est pris en charge.',
    'fields_listable_instructions' => 'Contrôle la visibilité de ce champ dans les colonnes.',
    'fieldset_import_fieldset_instructions' => 'Le jeu de champs à importer.',
    'fieldset_import_prefix_instructions' => 'Le préfixe à appliquer à chaque champ lors de leur importation. Ex. hero_',
    'fieldset_intro' => 'Les jeux de champs sont des compagnons optionnels des Blueprints qui vous permettent de créer des partiels utilisables dans ces Blueprints.',
    'fieldset_link_fields_prefix_instructions' => 'Tous les champs du jeu de champs lié seront préfixés avec ceci. Utile quand vous voulez importer les mêmes champs plusieurs fois.',
    'fieldsets_handle_instructions' => 'Comment vous allez faire référence à ce jeu de champs sur le frontal. Ne peut pas être facilement changé.',
    'fieldsets_title_instructions' => 'Décrit généralement quels champs seront inclus, comme "Bloc Image"',
    'focal_point_instructions' => 'La définition d’un point focal permet un recadrage dynamique des photos avec un sujet qui reste dans le cadre.',
    'focal_point_previews_are_examples' => 'Les aperçus de recadrage n’ont qu’une valeur d’exemple',
    'forgot_password_enter_email' => 'Entrez votre adresse e-mail afin que nous puissions envoyer un lien de réinitialisation du mot de passe.',
    'form_configure_blueprint_instructions' => 'Choisissez un Blueprint existant ou créez-en un nouveau.',
    'form_configure_email_from_instructions' => 'Laissez vide pour revenir à la valeur par défaut du site.',
    'form_configure_email_html_instructions' => 'La vue pour la version HTML de cet e-mail.',
    'form_configure_email_instructions' => 'Configurez les emails à envoyer quand de nouvelles soumissions de formulaires sont reçues.',
    'form_configure_email_reply_to_instructions' => 'Laissez le champ vide pour un retour à l’expéditeur.',
    'form_configure_email_subject_instructions' => 'Objet de l’e-mail.',
    'form_configure_email_text_instructions' => 'La vue pour la version Texte de cet e-mail.',
    'form_configure_email_to_instructions' => 'Adresse email du destinataire.',
    'form_configure_handle_instructions' => 'Comment vous allez faire référence à ce formulaire sur le frontal. Ne peut pas être facilement changé.',
    'form_configure_honeypot_instructions' => 'Le nom du champ à utiliser pour le pot de miel. Les pots de miel sont des champs spéciaux utilisés pour réduire le nombre de spams.',
    'form_configure_intro' => 'Les formulaires sont utilisés pour collecter des informations auprès de vos visiteurs et envoyer des événements et des notifications à votre équipe à chaque nouvelle soumission.',
    'form_configure_store_instructions' => 'Désactivez cette option si vous souhaitez que les soumissions ne déclenchent que les événements et envoient des notifications par email.',
    'form_configure_title_instructions' => 'Habituellement un appel à l’action, comme "Contactez-nous".',
    'getting_started_widget_blueprints' => 'Les Blueprints définissent les champs personnalisés utilisés pour créer et stocker votre contenu.',
    'getting_started_widget_collections' => 'Les collections contiennent les différents types de contenu de votre site.',
    'getting_started_widget_docs' => 'Apprenez à connaître Statamic pour bien comprendre ses capacités.',
    'getting_started_widget_header' => 'Débuter avec Statamic 3',
    'getting_started_widget_intro' => 'Pour commencer à construire votre nouveau site avec Statamic 3, nous vous recommandons de commencer par ces étapes.',
    'getting_started_widget_navigation' => 'Créez des listes de liens multi-niveaux qui pourront être utilisées pour afficher des barres de navigation, des pieds de page, etc.',
    'getting_started_widget_pro' => 'Statamic Pro donne l’accès à un nombre illimité de comptes utilisateurs, aux rôles, permissions, à l’intégration avec Git, aux révisions, aux multi-sites et bien plus encore !',
    'git_disabled' => 'L’intégration de Statamic Git est actuellement désactivée',
    'git_nothing_to_commit' => 'Rien à commettre, les chemins de contenu sont propres',
    'git_utility_description' => 'Gérez le contenu suivi Git',
    'global_search_open_using_slash' => 'Vous pouvez ouvrir la recherche globale en utilisant la touche <kbd>/</kbd>',
    'global_set_config_intro' => 'Les jeux de globales gèrent le contenu mis à disposition sur l’ensemble du site, comme les détails de l’entreprise, les coordonnées ou les paramètres frontaux.',
    'globals_blueprint_instructions' => 'Contrôle les champs à afficher lors de la modification des variables.',
    'globals_configure_handle_instructions' => 'Comment vous allez faire référence à ce jeu de globales sur le frontal. Ne peut pas être facilement changé.',
    'globals_configure_intro' => 'Un jeu de globales est un groupe de variables disponibles pour toutes les pages frontales du site.',
    'globals_configure_title_instructions' => 'Nous recommandons un nom représentatif des contenus de ce jeu, comme "Marque" ou "Société".',
    'licensing_error_invalid_domain' => 'Domaine invalide',
    'licensing_error_invalid_edition' => 'La licence est pour l’édition :edition',
    'licensing_error_no_domains' => 'Aucun domaine défini',
    'licensing_error_no_site_key' => 'Aucune clé de licence de site',
    'licensing_error_outside_license_range' => 'Licence valable pour les versions :start et :end',
    'licensing_error_unknown_site' => 'Site inconnu',
    'licensing_error_unlicensed' => 'Sans licence',
    'licensing_production_alert' => 'Veuillez acheter ou saisir une clé de licence valide pour que ce site respecte le contrat de licence.',
    'licensing_sync_instructions' => 'Les données de statamic.com sont synchronisées une fois par heure. Forcez une synchronisation pour voir toutes les modifications que vous avez apportées.',
    'licensing_trial_mode_alert' => 'Vous bénéficiez de fonctionnalités payantes ou d’addons qui nécessitent une licence avant de déployer ce site. Amusez-vous bien !',
    'licensing_utility_description' => 'Affichez et résolvez les détails de la licence.',
    'max_depth_instructions' => 'Définissez le nombre maximum de niveaux sur lesquels une page peut être imbriquée. Laissez vide pour aucune limite.',
    'max_items_instructions' => 'Définissez un nombre maxi d’éléments sélectionnables.',
    'navigation_configure_collections_instructions' => 'Activer le lien vers les entrées de ces collections.',
    'navigation_configure_handle_instructions' => 'Comment vous allez faire référence à cette navigation sur le frontal. Ne peut pas être facilement changé.',
    'navigation_configure_intro' => 'Les navigations sont des listes multi-niveaux de liens qui peuvent être utilisées pour construire des barres de navigation, des pieds de page, des plans de site et d’autres formes de navigation sur le frontal.',
    'navigation_configure_settings_intro' => 'Activer les liens vers les collections, définir une profondeur maximale et d’autres comportements.',
    'navigation_configure_title_instructions' => 'Nous recommandons un nom qui corresponde à son emplacement, comme "Nav Principale" ou "Nav Pied De Page".',
    'navigation_documentation_instructions' => 'En savoir plus sur la construction, la configuration et le rendu des navigations.',
    'navigation_link_to_entry_instructions' => 'Ajoutez un lien à une entrée. Activez le lien avec des collections supplémentaires dans votre configuration.',
    'navigation_link_to_url_instructions' => 'Ajouter un lien vers une URL interne ou externe. Activez le lien vers les entrées dans votre configuration.',
    'outpost_error_422' => 'Erreur de communication avec statamic.com.',
    'outpost_error_429' => 'Trop de demandes à statamic.com.',
    'outpost_issue_try_later' => 'Un problème est survenu lors de la communication avec statamic.com. Veuillez réessayer plus tard.',
    'phpinfo_utility_description' => 'Vérifiez vos paramètres de configuration PHP et les modules installés.',
    'publish_actions_create_revision' => 'Une révision sera créée à partir de la copie de travail. La révision actuelle ne changera pas.',
    'publish_actions_current_becomes_draft_because_scheduled' => 'Étant donné que la révision actuelle est publiée et que vous avez sélectionné une date dans le futur, une fois que vous aurez soumis la révision, elle restera en statut brouillon jusqu’à la date sélectionnée.',
    'publish_actions_publish' => 'Les modifications apportées à la copie de travail s’appliqueront à l’entrée et celle-ci sera publiée immédiatement.',
    'publish_actions_schedule' => 'Les modifications apportées à la copie de travail s’appliqueront à l’entrée et celle-ci paraîtra publiée à la date sélectionnée.',
    'publish_actions_unpublish' => 'La révision actuelle sera dépubliée.',
    'rename_asset_warning' => 'Renommer une ressource ne mettra à jour aucune des références qui pointent vers elle, ce qui pourrait entraîner l’apparition de liens brisés sur votre site.',
    'reset_password_notification_body' => 'Vous recevez cet email car nous avons reçu une demande de réinitialisation du mot de passe pour votre compte.',
    'reset_password_notification_no_action' => 'Si vous n’avez pas demandé de réinitialisation de mot de passe, aucune autre action n’est requise.',
    'reset_password_notification_subject' => 'Notification de réinitialisation du mot de passe',
    'role_change_handle_warning' => 'Changer l’identifiant ne mettra pas à jour les références à ce dernier dans les utilisateurs et les groupes.',
    'role_handle_instructions' => 'Comment vous allez faire référence à ce rôle sur le frontal. Ne peut pas être facilement changé.',
    'role_intro' => 'Les rôles sont des groupes d’autorisations d’accès et d’action qui peuvent être attribuées aux utilisateurs et aux groupes d’utilisateurs.',
    'role_title_instructions' => 'Habituellement un nom singulier, comme "Editeur" ou "Admin".',
    'search_utility_description' => 'Gérez et affichez des informations importantes sur les index de recherche de Statamic.',
    'session_expiry_enter_password' => 'Entrez votre mot de passe pour continuer là où vous vous êtes arrêté.',
    'session_expiry_logged_out_for_inactivity' => 'Vous avez été déconnecté parce que vous êtes resté inactif trop longtemps.',
    'session_expiry_logging_out_in_seconds' => 'Vous avez été inactif pendant un certain temps et vous serez déconnecté dans :seconds secondes. Cliquez pour prolonger votre session.',
    'session_expiry_new_window' => 'S’ouvre dans une nouvelle fenêtre. Revenez une fois que serez reconnecté.',
    'tab_sections_instructions' => 'Les champs de chaque section seront regroupés dans des onglets. Créez de nouveaux champs, réutilisez des champs existants ou importez des groupes entiers de champs à partir de jeux de champs existants.',
    'taxonomies_blueprints_instructions' => 'Les termes de cette taxonomie peuvent utiliser n’importe lequel de ces Blueprints.',
    'taxonomies_collections_instructions' => 'Les collections qui utilisent cette taxonomie.',
    'taxonomy_configure_handle_instructions' => 'Comment vous allez faire référence à cette taxonomie sur le frontal. Ne peut pas être facilement changé.',
    'taxonomy_configure_intro' => 'Une taxonomie est un système de classification des données autour d’un ensemble de caractéristiques uniques, telles que la catégorie ou la couleur.',
    'taxonomy_configure_title_instructions' => 'Nous recommandons un nom au pluriel, comme "Catégories" ou "Etiquettes".',
    'taxonomy_next_steps_configure_description' => 'Configurez des noms, associez des collections, définissez des Blueprints et plus encore.',
    'taxonomy_next_steps_create_term_description' => 'Créez votre premier terme ou écrasez une poignée de termes génériques, à vous de jouer.',
    'taxonomy_next_steps_documentation_description' => 'Apprenez-en plus au sujet des taxonomies, comment elles fonctionnent et comment les configurer.',
    'try_again_in_seconds' => '{0,1} Réessayez maintenant. | Réessayez dans :count secondes.',
    'updates_available' => 'Des mises à jour sont disponibles !',
    'user_groups_handle_instructions' => 'Comment vous allez faire référence à ce groupe d’utilisateurs sur le frontal. Ne peut pas être facilement changé.',
    'user_groups_intro' => 'Les groupes d’utilisateurs vous permettent d’organiser les utilisateurs et d’appliquer des rôles basés sur des autorisations dans leur ensemble.',
    'user_groups_role_instructions' => 'Affectez des rôles afin de donner aux utilisateurs de ce groupe toutes les autorisations correspondantes.',
    'user_groups_title_instructions' => 'Habituellement un nom au pluriel, comme "Editeurs" ou "Photographes".',
    'user_wizard_account_created' => 'Le compte d’utilisateur a été créé.',
    'user_wizard_email_instructions' => 'L’adresse e-mail sert également de nom d’utilisateur et doit être unique.',
    'user_wizard_intro' => 'Les utilisateurs peuvent se voir attribuer des rôles qui personnalisent leurs autorisations, leurs accès et leurs capacités via le Panneau de configuration.',
    'user_wizard_invitation_body' => 'Activez votre nouveau compte Statamic sur :site pour commencer à gérer ce site. Pour votre sécurité, le lien ci-dessous expire après :expiry heures. Ensuite, contactez l’administrateur du site pour obtenir un nouveau mot de passe.',
    'user_wizard_invitation_intro' => 'Envoyez un courrier électronique de bienvenue avec les détails d’activation du compte au nouvel utilisateur.',
    'user_wizard_invitation_share' => 'Copiez ces informations d’identification et partagez-les avec <code>:email</code> via votre méthode préférée.',
    'user_wizard_invitation_share_before' => 'Après avoir créé l’utilisateur, vous recevrez des détails à partager avec <code>:email</code> via votre méthode préférée.',
    'user_wizard_invitation_subject' => 'Activez votre nouveau compte Statamic sur :site',
    'user_wizard_name_instructions' => 'Vous pouvez laisser le nom vide si vous voulez laisser l’utilisateur le remplir.',
    'user_wizard_roles_groups_intro' => 'Les utilisateurs peuvent se voir attribuer des rôles qui personnalisent leurs autorisations, leurs accès et leurs capacités via le Panneau de configuration.',
    'user_wizard_super_admin_instructions' => 'Les super-administrateurs ont le contrôle complet et l’accès à tout ce qui se trouve dans le panneau de commande. Accordez ce rôle à bon escient.',
];
