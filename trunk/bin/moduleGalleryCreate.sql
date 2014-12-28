-- phpMyAdmin SQL Dump
-- version 3.1.3
-- http://www.phpmyadmin.net
--
-- Serveur: 209.222.235.12:3306
-- Version SVN: $Id: moduleGalleryCreate.sql 1368 2013-12-27 04:29:06Z ssoares $

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Table structure for table `Galleries`
--
DROP TABLE IF EXISTS `Galleries`;
CREATE TABLE IF NOT EXISTS `Galleries` (
  `G_ID` int(11) NOT NULL auto_increment,
  `G_Position` int(11) NOT NULL default '1',
  `G_Online` int(11) NOT NULL,
  `G_ImageID` int(11) NOT NULL,
  `G_CreationDate` date NOT NULL,
  `G_ModifiedDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `G_CategoryID` int(11) NOT NULL,
  PRIMARY KEY  (`G_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `GalleriesIndex`
--
DROP TABLE IF EXISTS `GalleriesIndex`;
CREATE TABLE IF NOT EXISTS `GalleriesIndex` (
  `GI_GalleryID` int(11) NOT NULL,
  `GI_LanguageID` int(11) NOT NULL,
  `GI_Title` varchar(255) NOT NULL,
  `GI_Description` text NOT NULL,
  `GI_ValUrl` varchar(255) default NULL,
  PRIMARY KEY  (`GI_GalleryID`,`GI_LanguageID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

INSERT INTO `ModuleViews` (`MV_ID`, `MV_Name`, `MV_ModuleID`) VALUES ('9003', 'gallery_menu', '9');

--
-- Table structure for table `Galleries_Images`
--
DROP TABLE IF EXISTS `Galleries_Images`;
CREATE TABLE IF NOT EXISTS `Galleries_Images` (
  `GI_GalleryID` int(11) NOT NULL,
  `GI_ImageID` int(11) NOT NULL,
  `GI_Position` int(11) NOT NULL default '1',
  `GI_Online` int(11) NOT NULL,
  `GI_CreationDate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`GI_GalleryID`,`GI_ImageID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `Images`
--

DROP TABLE IF EXISTS `Images`;
CREATE TABLE IF NOT EXISTS `Images` (
  `I_ID` int(11) NOT NULL auto_increment,
  `I_OriginalLink` varchar(255) NOT NULL,
  PRIMARY KEY  (`I_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ImagesIndex`
--

DROP TABLE IF EXISTS `ImagesIndex`;
CREATE TABLE IF NOT EXISTS `ImagesIndex` (
  `II_ImageID` int(11) NOT NULL,
  `II_LanguageID` int(11) NOT NULL,
  `II_Title` varchar(255) NOT NULL,
  `II_Description` text NOT NULL,
  PRIMARY KEY  (`II_ImageID`,`II_LanguageID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `Modules` (`M_ID`, `M_Title`, `M_MVCModuleTitle`, `M_Indexation`) VALUES
(9, 'Gallery', 'gallery', 'Galleries');

INSERT INTO Modules_ControllersActionsPermissions (MCAP_ModuleID, MCAP_ControllerTitle, MCAP_ControllerActionTitle, MCAP_PermissionTitle, MCAP_Position) VALUES
(9, 'index', 'list-categories', 'edit', 1),
(9, 'index', 'list', 'edit', 2);

REPLACE INTO `edith`.`Static_Texts` (`ST_Identifier`, `ST_LangID`, `ST_Value`, `ST_Type`, `ST_Desc_backend`, `ST_Editable`, `ST_ModuleID`) VALUES
('management_module_gallery_list_categories', '1', 'Catégories des galeries', 'cible', '', '0', '9'),
('management_module_gallery_list_categories', '2', 'Galleries category', 'cible', '', '0', '9'),
('gallery_categories_page_description', '1', 'Gestion des catégories de galerie.', 'cible', '', '0', '9'),
('gallery_categories_page_description', '2', 'Categories of gallery management', 'cible', '', '0', '9'),
('gallery_categories_page_title', '1', 'Catégories de galerie', 'cible', '', '0', '9'),
('gallery_categories_page_title', '2', 'Categories of gallery', 'cible', '', '0', '9'),
('gallery_button_add_category', '1', 'Ajouter une catégorie de galerie', 'cible', '', '0', '9'),
('gallery_button_add_category', '2', 'Add a category of gallery', 'cible', '', '0', '9');

INSERT INTO `Views` (`V_ID`, `V_Name`, `V_ZoneCount`, `V_Path`, `V_Image`) VALUES
(8,'Gallery menu de gauche',2,'template/galleryLeftMenu.phtml','image.png');


INSERT INTO ModuleViews (MV_ID, MV_Name, MV_ModuleID) VALUES
(9002,'details', 9),
(9001,'list', 9);

INSERT INTO ModuleViewsIndex (MVI_ModuleViewsID, MVI_LanguageID, MVI_ActionName) VALUES
(9002,1,'galerie'),
(9002,2,'gallery'),
(9001,1,'toutes'),
(9001,2,'all')
;

INSERT INTO Extranet_Resources (ER_ID, ER_ControlName) VALUES (9, 'gallery');

INSERT INTO Extranet_ResourcesIndex (ERI_ResourceID, ERI_LanguageID, ERI_Name) VALUES
(9, 1, 'Galerie'),
(9, 2, 'Gallery');

INSERT INTO Extranet_RolesResources (ERR_ID, ERR_RoleID, ERR_ResourceID, ERR_InheritedParentID) VALUES
(9001,1, 9, 0);
INSERT INTO Extranet_RolesResourcesIndex (ERRI_RoleResourceID, ERRI_LanguageID, ERRI_Name, ERRI_Description) VALUES
(9001,1, 'Gestionnaire de galerie', ''),
(9001,2, 'Gallery manager', '');

INSERT INTO Extranet_RolesResourcesPermissions (ERRP_RoleResourceID, ERRP_PermissionID) VALUES
(9001, 1);

INSERT INTO Categories (C_ID, C_ParentID, C_ModuleID, C_PageID, C_ShowInRss, C_RssItemsCount) VALUES
(9001,0, 9, 0, 0, 0);

INSERT INTO CategoriesIndex (CI_CategoryID, CI_LanguageID, CI_Title, CI_WordingShowAllRecords) VALUES
(9001, 1, 'Galerie Photo', ''),
(9001, 2, 'Photo Gallery', '');

INSERT INTO `Pages` (`P_ID`, `P_Position`, `P_ParentID`, `P_Home`, `P_LayoutID`, `P_ThemeID`, `P_ViewID`, `P_ShowSiteMap`, `P_ShowMenu`, `P_ShowTitle`, `P_Indexation`) VALUES
(9001, 3, 0, 0, 2, 1, 2, 1, 1, 1,1),
(9002, 1, 9001, 0, 2, 1, 2, 1, 1, 1,0);

INSERT INTO `PagesIndex` (`PI_PageID`, `PI_LanguageID`, `PI_PageIndex`, `PI_PageIndexOtherLink`, `PI_PageTitle`, `PI_TitleImageSrc`, `PI_TitleImageAlt`, `PI_MetaDescription`, `PI_MetaKeywords`,`PI_MetaOther`, `PI_Status`, `PI_Secure`) VALUES
(9001, 1, 'galerie-photos', '', 'Galerie photos', '', '', '', '', '', 1, 'non'),
(9001, 2, 'gallery-photos', '', 'Photos gallery', '', '', '', '', '', 1, 'non'),
(9002, 1, 'details-galerie', '', 'Details galerie', '', '', '', '', '', 1, 'non'),
(9002, 2, 'details-gallery', '', 'Gallery details', '', '', '', '', '', 1, 'non');

INSERT INTO `ModuleCategoryViewPage` (`MCVP_ID`, `MCVP_ModuleID`, `MCVP_CategoryID`, `MCVP_ViewID`, `MCVP_PageID`) VALUES
(9001, 9, 9001, 9001, 9001),
(9002, 9, 9001, 9002, 9002);

REPLACE INTO `Static_Texts` (`ST_Identifier`, `ST_LangID`, `ST_Value`, `ST_Type`, `ST_Desc_backend`, `ST_Editable`, `ST_ModuleID`) VALUES
('button_add_gallery', 1, 'Ajouter une galerie photos', 'cible', '', 0, 9),
('button_add_gallery', 2, 'Add a photo gallery', 'cible', '', 0, 9),
('form_select_option_view_gallery_details', 1, 'Détails d''une galerie', 'cible', '', 0, 9),
('form_select_option_view_gallery_details', 2, 'Gallery details', 'cible', '', 0, 9),
('form_select_option_view_gallery_list', 1, 'Toutes les galeries', 'cible', '', 0, 9),
('form_select_option_view_gallery_list', 2, 'All the galleries', 'cible', '', 0, 9),
('fo_gallery_details_help_message', 1, 'Cliquez sur les vignettes pour agrandir.', 'cible', '', 0, 9),
('fo_gallery_details_help_message', 2, 'Click on thumbnails to enlarge', 'cible', '', 0, 9),
('fo_gallery_details_information_message_noGallery', 1, 'Cette galerie n''existe pas.', 'cible', '', 0, 9),
('fo_gallery_details_information_message_noGallery', 2, 'This gallery does not exist.', 'cible', '', 0, 9),
('fo_gallery_details_information_message_noImage', 1, "Il n'y a pas d''image dans cette galerie.", 'cible', '', 0, 9),
('fo_gallery_details_information_message_noImage', 2, 'There are no images in this gallery.', 'cible', '', 0, 9),
('fo_gallery_information_message_noGallery', 1, "Il n'existe aucune galerie.", 'cible', '', 0, 9),
('fo_gallery_information_message_noGallery', 2, 'There is no gallery.', 'cible', '', 0, 9),
('gallery_manage_block_contents', 1, 'Gestion des galeries photos', 'cible', '', 0, 9),
('gallery_manage_block_contents', 2, 'Photos gallery management', 'cible', '', 0, 9),
('gallery_module_name', 1, 'Galerie photos', 'cible', '', 0, 9),
('gallery_module_name', 2, 'Photo Gallery', 'cible', '', 0, 9),
('header_add_gallery_description', 1, "Cette page vous permet d'ajouter une nouvelle galerie de photos.", 'cible', '', 0, 9),
('header_add_gallery_description', 2, 'This pqge is to add a new photo gallery.', 'cible', '', 0, 9),
('header_add_gallery_title', 1, "Ajout d'une galerie de photos", 'cible', '', 0, 9),
('header_add_gallery_title', 2, 'Add a new photo gallery', 'cible', '', 0, 9),
('header_add_image_title', 1, "Ajouter une nouvelle image dans la galerie.", 'cible', '', 0, 9),
('header_add_image_title', 2, 'Add a new image in the gallery.', 'cible', '', 0, 9),
('header_add_image_description', 1, "Cette page permet d'ajouter une nouvelle image dans la galerie.", 'cible', '', 0, 9),
('header_add_image_description', 2, 'This page allows you to add a new image in this gallery', 'cible', '', 0, 9),
('header_delete_gallery_description', 1, 'Cliquez sur le bouton oui pour supprimer définitivement cette galerie', 'cible', '', 0, 9),
('header_delete_gallery_description', 2, 'Click Yes to permanently delete this gallery', 'cible', '', 0, 9),
('header_delete_gallery_title', 1, "Suppression d'une galerie de photos", 'cible', '', 0, 9),
('header_delete_gallery_title', 2, 'Deleting a photo gallery', 'cible', '', 0, 9),
('header_delete_image_title', 1, 'Effacer une image', 'cible', '', 0, 9),
('header_delete_image_title', 2, 'Delete an image', 'cible', '', 0, 9),
('header_delete_image_description', 1, 'Effacer une image dans une galerie', 'cible', '', 0, 9),
('header_delete_image_description', 2, 'Delete an image from the gallery', 'cible', '', 0, 9),
('header_edit_gallery_description', 1, 'Cette page vous permet de modifier les informations de cette galerie', 'cible', '', 0, 9),
('header_edit_gallery_description', 2, 'This page allows you to edit the information in this gallery', 'cible', '', 0, 9),
('header_edit_gallery_title', 1, "Édition d'une galerie de photos", 'cible', '', 0, 9),
('header_edit_gallery_title', 2, 'Editing a photo gallery', 'cible', '', 0, 9),
('header_edit_image_title', 1, "Edition de l'image de la galerie", 'cible', '', 0, 9),
('header_edit_image_title', 2, 'Edit an image of the gallery', 'cible', '', 0, 9),
('header_edit_image_description', 1, "Edition de l'image de la galerie", 'cible', '', 0, 9),
('header_edit_image_description', 2, 'Edit an image of the gallery', 'cible', '', 0, 9),
('header_list_gallery_description', 1, 'Cette page permet de faire la gestion de toutes les galeries photos de ce site Web.', 'cible', '', 0, 9),
('header_list_gallery_description', 2, 'This page allows you to manage all the photo galleries within the web site.', 'cible', '', 0, 9),
('header_list_gallery_text', 1, 'Liste des galeries photos', 'cible', '', 0, 9),
('header_list_gallery_text', 2, 'Photo galeries', 'cible', '', 0, 9),
('management_module_gallery_list_catID_9001', 1, 'Galerie Photo', 'cible', '', 0, 9),
('management_module_gallery_list_catID_9001', 2, 'Photo gallery', 'cible', '', 0, 9),
('management_module_gallery_list_catID_9002', 1, 'Capacités personnalisées', 'cible', '', 0, 9),
('management_module_gallery_list_catID_9002', 2, 'Custom capabilities', 'cible', '', 0, 9),
('management_module_gallery_list',1,'Galeries photos','cible','', 0, 9),
('management_module_gallery_list',2,'Photo galleries','cible','', 0, 9),
('menu_gallery', 1, 'Galerie', 'client', '', 0, 9),
('menu_gallery', 2, 'Gallery', 'client', '', 0, 9),
('module_gallery', 1, 'Galerie', 'cible', '', 0, 9),
('module_gallery', 2, 'Gallery', 'cible', '', 0, 9),
('list_action_gallery_edit', 1, 'Editer la galerie', 'cible', '', 0, 9),
('list_action_gallery_edit', 2, 'Edit the gallery', 'cible', '', 0, 9),
('list_action_gallery_delete', 1, 'Supprimer la galerie', 'cible', '', 0, 9),
('list_action_gallery_delete', 2, 'Delete the gallery', 'cible', '', 0, 9),
('list_action_gallery_add_image', 1, 'Ajouter une image à la galerie', 'cible', '', 0, 9),
('list_action_gallery_add_image', 2, 'Add an image to the gallery', 'cible', '', 0, 9),
('list_gallery_empty_list', 1, '<em>Aucune galerie pour le moment.</em>', 'cible', '', 0, 9),
('list_gallery_empty_list', 2, '<em>No gallery right now.</em>', 'cible', '', 0, 9),
('list_gallery_empty_image_list', 1, 'Aucune image dans cette galerie', 'cible', '', 0, 9),
('list_gallery_empty_image_list', 2, 'No image in this gallery', 'cible', '', 0, 9),
('gallery_no_gallery', 1, 'Il n''y a aucune gallerie.', 'cible', '', 0, 9),
('gallery_no_gallery', 2, 'There is no gallery', 'cible', '', 0, 9),
('see_details_gallery_text', '1', 'Voire cette galerie', 'cible', '', 0, 9),
('see_details_gallery_text', '2', 'See this gallery', 'cible', '', 0, 9),
('form_select_option_zoneViews_8', '1', 'Galerie en détails', 'cible', '', '0', '0'),
('form_select_option_zoneViews_8', '2', 'Gallery details', 'cible', '', '0', '0'),
('form_select_option_view_gallery_gallery_menu', '1', 'Menu des galeries', 'cible', '', '0', '9'),
('form_select_option_view_gallery_gallery_menu', '2', 'Galleries menu', 'cible', '', '0', '9'),
('form_gallery_blockGallerey_label', '1', 'Montré seulement cette galerie', 'cible', '', '0', '9'),
('form_gallery_blockGallerey_label', '2', 'Show only this gallery', 'cible', '', '0', '9'),
('form_gallery_blockGallerey_None', '1', 'Non', 'cible', '', '0', '9'),
('form_gallery_blockGallerey_None', '2', 'No', 'cible', '', '0', '9'),
('form_gallery_blockGallerey_label', '1', 'Montré seulement cette galerie', 'cible', '', '0', '9'),
('form_gallery_blockGallerey_label', '2', 'Show only this gallery', 'cible', '', '0', '9'),
('form_gallery_blockGallerey_None', '1', 'Non', 'cible', '', '0', '9'),
('form_gallery_blockGallerey_None', '2', 'No', 'cible', '', '0', '9'),
('see_all_gallery_text', '1', 'Retour aux galleries', 'cible', '', '0', '9'),
('see_all_gallery_text', '2', 'Back to galleries', 'cible', '', '0', '9'),
('form_gallery_blockCategory_label', 1, 'Catégorie de ce bloc', 'cible', '', 0, 9),
('form_gallery_blockCategory_label', 2, 'Catégorie of this bloc', 'cible', '', 0, 9),
('categories_9_delete_page_title', '1', 'Supprimer une catégorie de galerie', 'cible', '', '0', '9'),
('categories_9_delete_page_title', '2', 'Delete a category of gallery', 'cible', '', '0', '9'),
('categories_categories_9_delete_page_description', '1', 'Cette page permet la suppression d''une catégorie de galerie', 'cible', '', '0', '9'),
('categories_categories_9_delete_page_description', '2', 'This page allows the deletion of a category of gallery', 'cible', '', '0', '9'),
('categories_9_delete_message_confirmation', '1', 'Etes-vous certain de vouloir supprimer cette catégorie?', 'cible', '', '0', '9'),
('categories_9_delete_message_confirmation', '2', 'Are you sure you want to delete this category?', 'cible', '', '0', '9');
