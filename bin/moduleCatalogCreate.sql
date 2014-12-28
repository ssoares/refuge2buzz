-- Version SVN: $Id: moduleCatalogCreate.sql 1559 2014-04-23 12:50:08Z ssoares $

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- --------------------------------------------------------

--
-- Table structure for table `AssociatedProducts`
--

DROP TABLE IF EXISTS `Catalog_AssociatedProducts`;
CREATE TABLE IF NOT EXISTS `Catalog_AssociatedProducts` (
  `AP_MainProductID` int(11) NOT NULL,
  `AP_RelatedProductID` int(11) NOT NULL,
  `AP_Seq` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY  (`AP_MainProductID`,`AP_RelatedProductID`),
  KEY `MainProd_Product` (`AP_MainProductID`),
  KEY `AssocProd_Product` (`AP_RelatedProductID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Categories`
--

DROP TABLE IF EXISTS `Catalog_CategoriesData`;
CREATE TABLE IF NOT EXISTS `Catalog_CategoriesData` (
  `CC_ID` int(11) NOT NULL auto_increment,
  `CC_ParentId` INT(11) NULL COMMENT 'elem:select|src:categories',
  `CC_imageCat` varchar(255) default NULL COMMENT 'elem:image',
  `CC_Seq` INT(11) NULL,
  PRIMARY KEY  (`CC_ID`),
  KEY `CC_CI` (`CC_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `CategoriesIndex`
--

DROP TABLE IF EXISTS `Catalog_CategoriesIndex`;
CREATE TABLE IF NOT EXISTS `Catalog_CategoriesIndex` (
  `CCI_CategoryID` int(11) NOT NULL,
  `CCI_LanguageID` int(2) NOT NULL default '1',
  `CCI_Name` varchar(255) default NULL COMMENT 'seq:1',
  `CCI_ValUrl` varchar(255) default NULL COMMENT 'exclude:true',
  `CCI_MetaId` int(11) default NULL COMMENT 'exclude:true',
  `CCI_Seq` int(11) DEFAULT '100' COMMENT 'exclude:true',
  `CCI_Description` TEXT NULL COMMENT 'elem:tiny',
  PRIMARY KEY  (`CCI_CategoryID`,`CCI_LanguageID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ItemIndex`
--

DROP TABLE IF EXISTS `Catalog_ItemsIndex`;
CREATE TABLE IF NOT EXISTS `Catalog_ItemsIndex` (
  `II_ItemID` int(11) NOT NULL,
  `II_LanguageID` int(11) NOT NULL,
  `II_Name` varchar(255) default NULL,
  `II_MetaId` int(11) default NULL,
  `II_Seq` int(11) default 100,
  PRIMARY KEY  (`II_ItemID`,`II_LanguageID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Items`
--

DROP TABLE IF EXISTS `Catalog_ItemsData`;
CREATE TABLE IF NOT EXISTS `Catalog_ItemsData` (
  `I_ID` int(11) NOT NULL auto_increment,
  `I_ProductID` int(11) default NULL COMMENT 'elem:select|src:products',
  `I_ProductCode` varchar(255) default NULL,
  `I_DispLogged` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `I_NoAddToCart` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `I_Seq` INT(11) NULL DEFAULT '100' ,
  `I_PriceDetail` float default NULL,
  `I_PricePro` float default NULL,
  `I_PriceVol1` float default NULL,
  `I_PriceVol2` float default NULL,
  `I_PriceVol3` float default NULL,
  `I_LimitVol` int(11) default NULL,
  `I_Special` tinyint(1) default NULL COMMENT 'elem:checkbox',
  `I_DiscountPercent` float default NULL,
  `I_TaxProv` tinyint(1) default '1' COMMENT 'elem:checkbox',
  `I_TaxFed` tinyint(1) default '1' COMMENT 'elem:checkbox',
  PRIMARY KEY  (`I_ID`),
  KEY `Items_ItemIndex` (`I_ID`),
  KEY `Items_Produit` (`I_ProductID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Products`
--

DROP TABLE IF EXISTS `Catalog_ProductsData`;
CREATE TABLE IF NOT EXISTS `Catalog_ProductsData` (
  `P_ID` int(11) NOT NULL auto_increment,
  `P_CategoryId` int(11) NOT NULL COMMENT 'elem:select|src:category|group:productFormLeft|seq:2',
  `P_Photo` varchar(255) DEFAULT NULL COMMENT 'elem:image|group:productFormRight',
  `P_Number` varchar(100) DEFAULT NULL COMMENT 'group:productFormLeftBis',
  `P_Seq` INT(11) NULL COMMENT 'class:shortTextInput',
--   `P_Collection` INT(11) NULL DEFAULT '0' COMMENT 'elem:select|src:collections' ,
--   `P_CumulPoint` tinyint(1) default '1',
  PRIMARY KEY  (`P_ID`),
  KEY `Produit_ProduitIndex` (`P_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ProductsIndex`
--

DROP TABLE IF EXISTS `Catalog_ProductsIndex`;
CREATE TABLE IF NOT EXISTS `Catalog_ProductsIndex` (
  `PI_ProductID` int(11) NOT NULL,
  `PI_LanguageID` int(11) NOT NULL,
  `PI_Name` varchar(255) NOT NULL COMMENT 'seq:1|group:productFormLeft',
  `PI_Description` text COMMENT 'elem:tiny|group:productFormBottom|class:largeEditor',
  `PI_Options` text COMMENT 'elem:tiny|class:largeEditor',
  `PI_Notes` text COMMENT 'elem:tiny|class:largeEditor',
  `PI_MotsCles` VARCHAR(255) NULL DEFAULT NULL COMMENT 'exclude:true' ,
  `PI_ValUrl` VARCHAR(255) NULL DEFAULT NULL COMMENT 'exclude:true' ,
  `PI_MetaId` INT(11) NULL DEFAULT NULL COMMENT 'exclude:true' ,
  `PI_Seq` INT(11) NULL DEFAULT '100' COMMENT 'exclude:true' ,
  PRIMARY KEY  (`PI_ProductID`,`PI_LanguageID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE `Catalog_ProductsImages` (
  `CPI_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CPI_ProductId` int(11) NOT NULL,
  `CPI_Img` varchar(255) NOT NULL,
  `CPI_Seq` int(11) DEFAULT NULL,
  PRIMARY KEY (`CPI_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `SousCategories`
--

-- DROP TABLE IF EXISTS `Catalog_SousCategoriesData`;
-- CREATE TABLE IF NOT EXISTS `Catalog_SousCategoriesData` (
--   `SC_ID` int(11) NOT NULL auto_increment,
--   `SC_CategoryID` int(11) NOT NULL,
--   `SC_Seq` int(11) NULL default '100',
--   `SC_BannerGroupID` INT(11) NULL,
--   PRIMARY KEY  (`SC_ID`),
--   KEY `SC_Produit` (`SC_ID`),
--   KEY `SC_Cat` (`SC_CategoryID`)
-- ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `SousCategoriesIndex`
--

-- DROP TABLE IF EXISTS `Catalog_SubCategoriesIndex`;
-- CREATE TABLE IF NOT EXISTS `Catalog_SousCategoriesIndex` (
--   `SCI_SousCategoryID` int(11) NOT NULL,
--   `SCI_LanguageID` int(2) NOT NULL,
--   `SCI_Name` varchar(255) default NULL,
--   `SCI_ValUrl` varchar(255) default NULL,
--   `SCI_MetaId` int(11) default 100,
--   PRIMARY KEY  (`SCI_SousCategoryID`,`SCI_LanguageID`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Catalog_parameters`
--

DROP TABLE IF EXISTS `Catalog_Parameters`;
CREATE TABLE IF NOT EXISTS `Catalog_Parameters` (
  `CP_ID` INT(11) NOT NULL AUTO_INCREMENT ,
  `CP_ShippingFees` FLOAT NULL DEFAULT '9.95' ,
  `CP_ShippingFeesLimit` FLOAT NULL DEFAULT '300' ,
  `CP_MontantFraisCOD` FLOAT NULL DEFAULT '4.5' ,
  `CP_AdminOrdersEmail` VARCHAR(255) NULL DEFAULT NULL ,
  `CP_FreeItemID` INT(11) NULL DEFAULT NULL ,
  `CP_FreeMiniAmount` INT(11) NULL DEFAULT NULL ,
  `CP_BonusPointDollar` INT(11) NULL DEFAULT '2' ,
  `CP_TauxTaxeFed` FLOAT NULL DEFAULT NULL ,
  PRIMARY KEY (`CP_ID`)
) ENGINE = MyISAM DEFAULT CHARACTER SET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Catalog_CustomerProfiles`
--

-- DROP TABLE IF EXISTS `Catalog_CustomerProfiles`;
-- CREATE TABLE IF NOT EXISTS `Catalog_CustomerProfiles` (
--  `CP_CustomerProfileId` int(11) NOT NULL auto_increment,
--  `CP_GenericProfileId` int(11) NOT NULL,
--  `CP_CompanyName` varchar(255) NULL,
--  `CP_BillingAddressId` int(11) NOT NULL,
--  `CP_ShippingAddressId` int(11) NOT NULL,
--  `CP_hasAccount` tinyint(1) default '0',
--  `CP_AccountingNumber` int(11) NOT NULL,
--  `CP_NoProvTax` tinyint(1) default '0',
--  `CP_NoFedTax` tinyint(1) default '0',
--  PRIMARY KEY (`CP_CustomerProfileId`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Catalog_TaxesProv`
--


DROP TABLE IF EXISTS `Catalog_TaxeZone`;
CREATE TABLE IF NOT EXISTS `Catalog_TaxeZone` (
  `TZ_ID` int(11) NOT NULL auto_increment,
  `TZ_CountryCode` char(2) NOT NULL,
  `TZ_Country` char(30) NOT NULL,
  `TZ_ProvCode` char(2) NOT NULL,
  `TZ_Province` char(30) NOT NULL,
  `TZ_GroupName` char(10) NOT NULL,
  `TZ_TaxValue1` float NOT NULL default '0',
  `TZ_TaxValue2` float NOT NULL default '0',
  `TZ_TaxValue3` float NOT NULL default '0',
  `TZ_TaxValue4` float NOT NULL default '0',
  `TZ_TaxValue5` float NOT NULL default '0',
  PRIMARY KEY  (`TZ_ProvCode`,`TZ_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `Catalog_TaxeZone`
--

INSERT INTO `Catalog_TaxeZone` (`TZ_ID`, `TZ_CountryCode`, `TZ_Country`, `TZ_ProvCode`, `TZ_Province`, `TZ_GroupName`, `TZ_TaxValue1`, `TZ_TaxValue2`, `TZ_TaxValue3`, `TZ_TaxValue4`, `TZ_TaxValue5`) VALUES
(13, 'CA', 'Canada', 'YT', 'Yukon Territory', 'T.V.H.', 0.13, 0, 0, 0, 0),
(12, 'CA', 'Canada', 'SK', 'Saskatchewan', 'T.V.H.', 0.13, 0, 0, 0, 0),
(11, 'CA', 'Canada', 'QC', 'Quebec', 'QC', 0.05, 0.085, 0, 0, 0),
(10, 'CA', 'Canada', 'PE', 'Prince Edward Island', 'T.V.H.', 0.13, 0, 0, 0, 0),
(9, 'CA', 'Canada', 'ON', 'Ontario', 'T.V.H.', 0.13, 0, 0, 0, 0),
(8, 'CA', 'Canada', 'NU', 'Nunavut', 'T.V.H.', 0.13, 0, 0, 0, 0),
(7, 'CA', 'Canada', 'NT', 'Northwest Territories', 'T.V.H.', 0.13, 0, 0, 0, 0),
(6, 'CA', 'Canada', 'NS', 'Nova Scotia', 'T.V.H.', 0.13, 0, 0, 0, 0),
(5, 'CA', 'Canada', 'NB', 'New Brunswick', 'T.V.H.', 0.13, 0, 0, 0, 0),
(4, 'CA', 'Canada', 'NF', 'Newfoundland', 'T.V.H.', 0.13, 0, 0, 0, 0),
(3, 'CA', 'Canada', 'MB', 'Manitoba', 'T.V.H.', 0.13, 0, 0, 0, 0),
(2, 'CA', 'Canada', 'BC', 'British Columbia', 'T.V.H.', 0.13, 0, 0, 0, 0),
(1, 'CA', 'Canada', 'AB', 'Alberta', 'T.V.H.', 0.13, 0, 0, 0, 0);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

--
-- Données pour activer module et les liens dans le back end
--

REPLACE INTO Modules (M_ID, M_Title, M_MVCModuleTitle, M_Indexation) VALUES (14, 'Catalogue', 'catalog', 'ProductsData');

INSERT INTO Modules_ControllersActionsPermissions (MCAP_ModuleID, MCAP_ControllerTitle, MCAP_ControllerActionTitle, MCAP_PermissionTitle, MCAP_Position) VALUES
(14, 'index', 'categories', 'edit', 1),
(14, 'index', 'products', 'edit', 3),
(14, 'index', 'items', 'edit', 4),
(14, 'parameters', 'parameters', 'edit', 6);

INSERT INTO ModuleViews (MV_ID, MV_Name, MV_ModuleID) VALUES
(14001, 'detail', 14),
(14002, 'list', 14),
(14003, 'search_results', 14);

INSERT INTO `ModuleViewsIndex` (`MVI_ModuleViewsID`, `MVI_LanguageID`, `MVI_ActionName`) VALUES
(14001, 1, 'detail'),
(14001, 2, 'details'),
(14002, 1, 'toutes'),
(14002, 2, 'list-all'),
(14003, 2, 'search-results'),
(14003, 1, 'resultats-recherche');

INSERT INTO Pages (P_ID, P_Position, P_ParentID, P_Home, P_LayoutID, P_ThemeID, P_ViewID, P_ShowSiteMap, P_ShowMenu, P_ShowTitle, P_BannerGroupID) VALUES
(14001, 6, 0, 0, 2, 1, 2, 1, 1, 1, NULL),
(14002, 1, 14001, 0, 2, 1, 2, 1, 1, 0, NULL);

INSERT INTO PagesIndex (PI_PageID, PI_LanguageID, PI_PageIndex, PI_PageIndexOtherLink, PI_PageTitle, PI_TitleImageSrc, PI_TitleImageAlt, PI_MetaDescription, PI_MetaKeywords, PI_MetaOther, PI_Status, PI_Secure) VALUES
(14001, 1, 'catalogue', '', 'Catalogue', '', '', '', '', '', 1, 'non'),
(14001, 2, 'catalog-en', '', 'Catalog', '', '', '', '', '', 1, 'non');

INSERT INTO ModuleCategoryViewPage (MCVP_ModuleID, MCVP_CategoryID, MCVP_ViewID, MCVP_PageID) VALUES
(14, 0, 14003, 14001),
(14, 0, 14002, 14001);

INSERT INTO Extranet_Resources (ER_ID, ER_ControlName) VALUES (14, 'catalog');

INSERT INTO Extranet_ResourcesIndex (ERI_ResourceID, ERI_LanguageID, ERI_Name) VALUES
(14, 1, 'Catalogue'),
(14, 2, 'catalog');

INSERT INTO Extranet_RolesResources (ERR_ID, ERR_RoleID, ERR_ResourceID, ERR_InheritedParentID) VALUES
(1403,1, 14, 0);

INSERT INTO Extranet_RolesResourcesIndex (ERRI_RoleResourceID, ERRI_LanguageID, ERRI_Name, ERRI_Description) VALUES
(1403,1, 'Gestionnaire de catalogue', ''),
(1403,2, 'Catalog manager', '');

INSERT INTO Extranet_RolesResourcesPermissions (ERRP_RoleResourceID, ERRP_PermissionID) VALUES
(1403, 1);

--
-- Structure de la table `Catalog_ItemsPromo`
--

CREATE TABLE IF NOT EXISTS `Catalog_ItemsPromo` (
  `IP_ID` int(11) NOT NULL auto_increment,
  `IP_ItemId` int(11) NOT NULL,
  `IP_Price` float NOT NULL,
  `IP_ConditionItemId` int(11) NOT NULL,
  `IP_NbItem` int(5) NOT NULL,
  `IP_ConditionAmount` float NOT NULL,
  PRIMARY KEY  (`IP_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--

REPLACE INTO `Static_Texts` (`ST_Identifier`, `ST_LangID`, `ST_Value`, `ST_Type`, `ST_Desc_backend`, `ST_Editable`, `ST_ModuleID`) VALUES
('catalog_module_name', 1, 'Catalogue', 'cible', '', 0, 14),
('catalog_module_name', 2, 'Catalog', 'cible', '', 0, 14),
('management_module_catalog_categories', 1, 'Catégories du catalogue', 'cible', '', 0, 14),
('management_module_catalog_categories', 2, 'Catalog categories', 'cible', '', 0, 14),
('management_module_catalog_list_sub_cat', 1, 'Sous-catégories', 'cible', '', 0, 14),
('management_module_catalog_list_sub_cat', 2, 'Subcategories', 'cible', '', 0, 14),
('management_module_catalog_products', 1, 'Produits', 'cible', '', 0, 14),
('management_module_catalog_products', 2, 'Products', 'cible', '', 0, 14),
('management_module_catalog_items', 1, 'Items', 'cible', '', 0, 14),
('management_module_catalog_items', 2, 'Items', 'cible', '', 0, 14),
('header_list_categories_text', 1, 'Liste des catégories du catalogue', 'cible', '', 0, 14),
('header_list_categories_text', 2, 'List of the catalog categories', 'cible', '', 0, 14),
('header_list_categories_description', 1, 'Cliquez sur <b>Ajouter une catégorie</b> pour <br>créer une catégorie.<br><br>Vous pouvez <b>rechercher par mots-clés</b> parmi la liste<br>des catégories. Pour revenir à la liste complète,<br>cliquez sur <b>Voir la liste complète</b>.<br><br>Vous pouvez <b>modifier ou supprimer une<br>catégories</b> en cliquant sur l\'icône <img src="/extranet/icons/list_actions_icon.png" align=middle>.', 'cible', '', 0, 14),
('header_list_categories_description', 2, 'This page is to manage all the news.', 'cible', '', 0, 14),
('header_add_categories_text', 1, 'Ajouter une catégorie', 'cible', '', 0, 14),
('header_add_categories_text', 2, 'Add a new category', 'cible', '', 0, 14),
('header_add_categories_description', 1, "Cette page permet d'ajouter une nouvelle catégorie au catalogue.", 'cible', '', 0, 14),
('header_add_categories_description', 2, 'This page is to add a new category to the catalog.', 'cible', '', 0, 14),
('form_category_name_label', 1, 'Nom de la catégorie', 'cible', '', 0, 14),
('form_category_name_label', 2, 'Category name', 'cible', '', 0, 14),
('form_category_meta_label', 1, 'Id des données META associées', 'cible', '', 0, 14),
('form_category_meta_label', 2, 'Id of the associated META data', 'cible', '', 0, 14),
('form_category_logo_label', 1, 'Logo', 'cible', '', 0, 14),
('form_category_logo_label', 2, 'Logo', 'cible', '', 0, 14),
('header_edit_categories_text', 1, 'Modifier une catégorie', 'cible', '', 0, 14),
('header_edit_categories_text', 2, 'Edit a category', 'cible', '', 0, 14),
('header_edit_categories_description', 1, "Cette page permet d'éditer les informations de la catégorie.", 'cible', '', 0, 14),
('header_edit_categories_description', 2, 'This page is to edit data of the current category.', 'cible', '', 0, 14),
('delete_catalog_category_confirmation', 1, 'Voulez-vous supprimer la catégorie ', 'cible', '', 0, 14),
('delete_catalog_category_confirmation', 2, 'Do you want to delete ', 'cible', '', 0, 14),
('delete_catalog_category_noexist', 1, "Cette catégorie n'existe pas.", 'cible', '', 0, 14),
('delete_catalog_category_noexist', 2, 'This category does not exist.', 'cible', '', 0, 14),
('form_select_option_view_catalog_detail', 1, "Détails d'une galerie", 'cible', '', 0, 14),
('form_select_option_view_catalog_detail', 2, 'Details of a gallery', 'cible', '', 0, 14),
('catalog_category_block_page', 1, "Catégorie par défault", 'cible', '', 0, 14),
('catalog_category_block_page', 2, 'Default category', 'cible', '', 0, 14),
('form_search_catalog_keywords_label', 1, 'Mots-clés', 'cible', '', 0, 14),
('form_search_catalog_keywords_label', 2, 'Keywords', 'cible', '', 0, 14),
('header_list_subcategories_text', 1, 'Liste des sous-catégories du catalogue', 'cible', '', 0, 14),
('header_list_subcategories_text', 2, 'List of the catalog subcategories', 'cible', '', 0, 14),
('header_list_subcategories_description', 1, 'Cliquez sur <b>Ajouter une sous-catégorie</b> pour <br>créer une sous-catégorie.<br><br>Vous pouvez <b>rechercher par mots-clés</b> parmi la liste<br>des sous-catégories. Pour revenir à la liste complète,<br>cliquez sur <b>Voir la liste complète</b>.<br><br>Vous pouvez <b>modifier ou supprimer une<br>sous-catégories</b> en cliquant sur l\'icône <img src="/extranet/icons/list_actions_icon.png" align=middle>.', 'cible', '', 0, 14),
('header_list_subcategories_description', 2, 'This page is to manage all the news.', 'cible', '', 0, 14),
('list_column_SC_ID', 1, 'Id de la sous-catégorie', 'cible', '', 0, 14),
('list_column_SC_ID', 2, 'Subcategory id', 'cible', '', 0, 14),
('list_column_SCI_Name', 1, 'Nom de la sous-catégorie', 'cible', '', 0, 14),
('list_column_SCI_Name', 2, 'Subcategory name', 'cible', '', 0, 14),
('button_add_subcategory', 1, 'Ajouter une sous-catégorie', 'cible', '', 0, 14),
('button_add_subcategory', 2, 'Add Subcategory', 'cible', '', 0, 14),
('header_add_subcategories_text', 1, 'Ajouter une sous-catégorie', 'cible', '', 0, 14),
('header_add_subcategories_text', 2, 'Add a new subcategory', 'cible', '', 0, 14),
('header_add_subcategories_description', 1, "Cette page permet d'ajouter une nouvelle sous-catégorie au catalogue.", 'cible', '', 0, 14),
('header_add_subcategories_description', 2, 'This page is to add a new subcategory to the catalog.', 'cible', '', 0, 14),
('form_subcategory_name_label', 1, 'Nom de la sous-catégorie', 'cible', '', 0, 14),
('form_subcategory_name_label', 2, 'Subcategory name', 'cible', '', 0, 14),
('form_select_category_label', 1, 'Appartient à la catégorie:', 'cible', '', 0, 14),
('form_select_category_label', 2, 'Associated to the category:', 'cible', '', 0, 14),
('header_edit_subcategories_text', 1, 'Modifier une sous-catégorie', 'cible', '', 0, 14),
('header_edit_subcategories_text', 2, 'Edit a subcategory', 'cible', '', 0, 14),
('header_edit_subcategories_description', 1, "Cette page permet d'éditer les informations de la sous-catégorie.", 'cible', '', 0, 14),
('header_edit_subcategories_description', 2, 'This page is to edit data of the current subcategory.', 'cible', '', 0, 14),
('delete_catalog_subcategory_confirmation', 1, 'Voulez-vous supprimer la sous-catégorie ', 'cible', '', 0, 14),
('delete_catalog_subcategory_confirmation', 2, 'Do you want to subdelete ', 'cible', '', 0, 14),
('delete_catalog_subcategory_noexist', 1, "Cette sous-catégorie n'existe pas.", 'cible', '', 0, 14),
('delete_catalog_subcategory_noexist', 2, 'This subcategory does not exist.', 'cible', '', 0, 14);

REPLACE INTO `Static_Texts` (`ST_Identifier`, `ST_LangID`, `ST_Value`, `ST_Type`, `ST_Desc_backend`, `ST_Editable`, `ST_ModuleID`) VALUES
('header_add_products_text', 1, "Ajout d'un produit", 'cible', '', 0, 14),
('header_add_products_text', 2, 'Add a new product', 'cible', '', 0, 14),
('header_add_products_description', 1, 'Cette page permet d\'ajouter un nouveau produit.', 'cible', '', 0, 14),
('header_add_products_description', 2, 'This page is to add a new product.', 'cible', '', 0, 14),
('delete_catalog_products_confirmation', 1, 'Êtes-vous sûr de vouloir supprimer le produit', 'cible', '', 0, 14),
('delete_catalog_products_confirmation', 2, 'Are you sure you want to delete', 'cible', '', 0, 14),
('delete_catalog_products_noexist', 1, 'Ce produit n\'existe pas.', 'cible', '', 0, 14),
('delete_catalog_products_noexist', 2, 'This product does not exist.', 'cible', '', 0, 14),
('header_edit_products_text', 1, "Editer un produit", 'cible', '', 0, 14),
('header_edit_products_text', 2, 'Edit a product', 'cible', '', 0, 14),
('header_edit_products_description', 1, 'Cette page permet d\'éditer un produit.', 'cible', '', 0, 14),
('header_edit_products_description', 2, 'This page is to edit a product.', 'cible', '', 0, 14),
('header_list_products_text', 1, 'Liste des produits', 'cible', '', 0, 14),
('header_list_products_text', 2, 'Product list', 'cible', '', 0, 14),
('header_list_products_description', 1, 'Cette vous permet de gérer les produits. <p>Sélectionner un produit dans la liste ou ajoutez-en un nouveau.</p>', 'cible', '', 0, 14),
('header_list_products_description', 2, 'This page is to manage the products. <p>Select a product or add a new one.</p>', 'cible', '', 0, 14),
('button_add_products', 1, 'Ajouter un produit', 'cible', '', 0, 14),
('button_add_products', 2, 'Add product', 'cible', '', 0, 14),
('product_label_name', 1, 'Nom du produit', 'cible', '', 0, 14),
('product_label_name', 2, 'Name of the product', 'cible', '', 0, 14),
('form_product_accumulation_label', 1, 'Ce produit donne des Cumule-Points', 'cible', '', 0, 14),
('form_product_accumulation_label', 2, 'This product gives "Cumule-Points"', 'cible', '', 0, 14),
('form_product_isnew_label', 1, 'Afficher ce produit dans les nouveautés', 'cible', '', 0, 14),
('form_product_isnew_label', 2, 'Display this product as a new one.', 'cible', '', 0, 14),
('subform_public_legend', 1, 'Grand Public', 'cible', '', 0, 14),
('subform_public_legend', 2, 'Public', 'cible', '', 0, 14),
('product_label_descriptionPublic', 1, 'Description', 'cible', '', 0, 14),
('product_label_descriptionPublic', 2, 'Description', 'cible', '', 0, 14),
('product_label_notePublic', 1, 'Note supplémentaire (post-it)', 'cible', '', 0, 14),
('product_label_notePublic', 2, 'Additionnal note (post-it)', 'cible', '', 0, 14),
('product_label_technical_specs', 1, 'Fiche technique (PDF)', 'cible', '', 0, 14),
('product_label_technical_specs', 2, 'Specs sheet (PDF)', 'cible', '', 0, 14),
('subform_professional_legend', 1, 'Professionnels', 'cible', '', 0, 14),
('subform_professional_legend', 2, 'Professionals', 'cible', '', 0, 14),
('product_label_descriptionPro', 1, 'Description', 'cible', '', 0, 14),
('product_label_descriptionPro', 2, 'Description', 'cible', '', 0, 14),
('product_label_notePro', 1, 'Note supplémentaire (post-it)', 'cible', '', 0, 14),
('product_label_notePro', 2, 'Additionnal note (post-it)', 'cible', '', 0, 14),
('product_label_tool_promo', 1, 'Outil promotionnel (PDF)', 'cible', '', 0, 14),
('product_label_tool_promo', 2, 'Promotion tool (PDF)', 'cible', '', 0, 14),
('association_set_selectOne', 1, '-- Choisir un produit --', 'cible', '', 0, 14),
('association_set_selectOne', 2, '-- Select a product --', 'cible', '', 0, 14),
('form_products_subcat_label', 1, 'Associé à la sous-catégorie:', 'cible', '', 0, 14),
('form_products_subcat_label', 2, 'Associated to the subcategory:', 'cible', '', 0, 14),
('form_product_keywords_label', 1, 'Mots-clés', 'cible', '', 0, 14),
('form_product_keywords_label', 2, 'Keywords', 'cible', '', 0, 14);


REPLACE INTO `Static_Texts` (`ST_Identifier`, `ST_LangID`, `ST_Value`, `ST_Type`, `ST_Desc_backend`, `ST_Editable`, `ST_ModuleID`) VALUES
('header_list_items_text', 1, "Liste des items", 'cible', '', 0, 14),
('header_list_items_text', 2, 'Items list', 'cible', '', 0, 14),
('header_list_items_description', 1, "Cette page permet de gérer la liste des items associés aux produits.", 'cible', '', 0, 14),
('header_list_items_description', 2, 'This page is to manage items', 'cible', '', 0, 14),
('list_column_I_ID', 1, "Id de l'item", 'cible', '', 0, 14),
('list_column_I_ID', 2, 'Item Id', 'cible', '', 0, 14),
('list_column_II_Name', 1, "Nom de l'item", 'cible', '', 0, 14),
('list_column_II_Name', 2, 'Item name', 'cible', '', 0, 14),
('button_add_items', 1, "Ajouter un item", 'cible', '', 0, 14),
('button_add_items', 2, 'Add item', 'cible', '', 0, 14),
('header_add_items_text', 1, "Ajouter un item", 'cible', '', 0, 14),
('header_add_items_text', 2, 'Add an item', 'cible', '', 0, 14),
('header_add_items_description', 1, "Renseigner les champs du formulaire.", 'cible', '', 0, 14),
('header_add_items_description', 2, 'Fill the form to add data.', 'cible', '', 0, 14),
('item_label_name', 1, "Nom de l'item", 'cible', '', 0, 14),
('item_label_name', 2, 'Item name', 'cible', '', 0, 14),
('form_item_products_label', 1, "Associé au produit", 'cible', '', 0, 14),
('form_item_products_label', 2, 'Associated to the product', 'cible', '', 0, 14),
('form_product_code_label', 1, "Code produit", 'cible', '', 0, 14),
('form_product_code_label', 2, 'Product code', 'cible', '', 0, 14),
('subform_itemprices_legend', 1, "Prix par volume", 'cible', '', 0, 14),
('subform_itemprices_legend', 2, 'Prices by volume', 'cible', '', 0, 14),
('form_item_qty_labels', 1, "Quantité", 'cible', '', 0, 14),
('form_item_qty_label', 2, 'Quantity', 'cible', '', 0, 14),
('form_item_prices_label', 1, "Prix de vente", 'cible', '', 0, 14),
('form_item_prices_label', 2, 'Sells price', 'cible', '', 0, 14),
('form_item_limitvol1_label', 1, "De 1 à: ", 'cible', '', 0, 14),
('form_item_limitvol1_label', 2, 'From 1 to: ', 'cible', '', 0, 14),
('form_item_limitvol2_label', 1, "jusqu'à: ", 'cible', '', 0, 14),
('form_item_limitvol2_label', 2, 'to: ', 'cible', '', 0, 14),
('form_item_priceVol3_label', 1, "Et plus ", 'cible', '', 0, 14),
('form_item_priceVol3_label', 2, 'And more ', 'cible', '', 0, 14),
('form_item_special_label', 1, "Item en spécial", 'cible', '', 0, 14),
('form_item_special_label', 2, 'Item promo', 'cible', '', 0, 14),
('form_item_discount_label', 1, "Rabais en pourcentage", 'cible', '', 0, 14),
('form_item_discount_label', 2, 'Discount percentage', 'cible', '', 0, 14),
('form_item_taxprov_label', 1, "Taxe provinciale applicable", 'cible', '', 0, 14),
('form_item_taxprov_label', 2, 'Provincial tax applicable', 'cible', '', 0, 14),
('form_item_taxfed_label', 1, "Taxe fédérale applicable", 'cible', '', 0, 14),
('form_item_taxfed_label', 2, 'Federal tax applicable', 'cible', '', 0, 14),
('header_edit_items_text', 1, "Modification de l'item", 'cible', '', 0, 14),
('header_edit_items_text', 2, 'Edit item', 'cible', '', 0, 14),
('header_edit_items_description', 1, "Cette page permet de modifier des informations de l'item séléctionné.", 'cible', '', 0, 14),
('header_edit_items_description', 2, 'This page is to edit data of the selected item.', 'cible', '', 0, 14),
('form_item_pricedetail_label', 1, "Prix au détail", 'cible', '', 0, 14),
('form_item_pricedetail_label', 2, 'Price detail', 'cible', '', 0, 14),
('form_item_specialPrice_label', 1, "Prix spécial", 'cible', '', 0, 14),
('form_item_specialPrice_label', 2, 'Special price', 'cible', '', 0, 14),
('form_item_pricepro_label', 1, "Prix pour les professionnels", 'cible', '', 0, 14),
('form_item_pricepro_label', 2, 'Price for retailers', 'cible', '', 0, 14),
('header_delete_items_text', 1, "Suppression d'un item", 'cible', '', 0, 14),
('header_delete_items_text', 2, 'Deletion of an item', 'cible', '', 0, 14),
('products_no_product', 1, "Il n'y a actuellement aucun produit dans cette catégorie.", 'client', '', 0, 14),
('products_no_product', 2, 'There is no product for this category.', 'client', '', 0, 14),
('form_select_option_view_catalog_list', 2, 'List of products', 'cible', '', 0, 14),
('form_select_option_view_catalog_list', 1, 'Liste de produits', 'cible', '', 0, 14),
('form_select_option_view_catalog_list_new', 1, 'Liste des nouveaux produits', 'cible', '', 0, 14),
('form_select_option_view_catalog_list_new', 2, 'List of new products', 'cible', '', 0, 14);

REPLACE INTO `Static_Texts` (`ST_Identifier`, `ST_LangID`, `ST_Value`, `ST_Type`, `ST_Desc_backend`, `ST_Editable`, `ST_ModuleID`) VALUES
('form_parameters_montant_label', 1, "Frais de transport", 'cible', '', 0, 14),
('form_parameters_montant_label', 2, 'Transportation fees', 'cible', '', 0, 14),
('form_parameters_limit_transport_label', 1, "Limit des frais de transport", 'cible', '', 0, 14),
('form_parameters_limit_transport_label', 2, 'Limit transportation costs', 'cible', '', 0, 14),
('form_parameters_COD_label', 1, "Frais pour le COD", 'cible', '', 0, 14),
('form_parameters_COD_label', 2, 'Fees for COD', 'cible', '', 0, 14),
('form_parameters_email_label', 1, "Courriel pour les commandes", 'cible', '', 0, 14),
('form_parameters_email_label', 2, 'Email for orders', 'cible', '', 0, 14),
('form_parameters_free_item_label', 1, "Item gratuit", 'cible', '', 0, 14),
('form_parameters_free_item_label', 2, 'Free item', 'cible', '', 0, 14),
('form_parameters_free_item_minimum_label', 1, "Montant minimum pour l'item gratuit", 'cible', '', 0, 14),
('form_parameters_free_item_minimum_label', 2, 'Minimum amount for the item free', 'cible', '', 0, 14),
('form_parameters_bonus_label', 1, "Point bonis par dollar d'achat", 'cible', '', 0, 14),
('form_parameters_bonus_label', 2, 'Bonus Point per dollar', 'cible', '', 0, 14),
('form_parameters_taxe_label', 1, "Taxe fédérale", 'cible', '', 0, 14),
('form_parameters_taxe_label', 2, 'Federal tax', 'cible', '', 0, 14),
('header_list_parameters_text', 1, "Liste des paramètres", 'cible', '', 0, 14),
('header_list_parameters_text', 2, 'Parameters list', 'cible', '', 0, 14),
('header_list_parameters_description', 1, "Gestion des préférences de commande", 'cible', '', 0, 14),
('header_list_parameters_description', 2, 'Management command parameters', 'cible', '', 0, 14),
('header_edit_parameters_text', 1, "Modification", 'cible', '', 0, 14),
('header_edit_parameters_text', 2, 'Edit', 'cible', '', 0, 14),
('header_edit_parameters_description', 1, "Cette page permet de modifier les préférences de commande.", 'cible', '', 0, 14),
('header_edit_parameters_description', 2, 'This is the page to edit the command parameters.', 'cible', '', 0, 14),
('list_column_CP_ShippingFees', 1, "Frais de transport", 'cible', '', 0, 14),
('list_column_CP_ShippingFees', 2, 'Transportation fees', 'cible', '', 0, 14),
('list_column_CP_ShippingFeesLimit', 1, "Limit des frais de transport", 'cible', '', 0, 14),
('list_column_CP_ShippingFeesLimit', 2, 'Limit transportation costs', 'cible', '', 0, 14),
('list_column_CP_MontantFraisCOD', 1, "Frais pour le COD", 'cible', '', 0, 14),
('list_column_CP_MontantFraisCOD', 2, 'Fees for COD', 'cible', '', 0, 14),
('list_column_CP_AdminOrdersEmail', 1, "Courriel pour les commandes", 'cible', '', 0, 14),
('list_column_CP_AdminOrdersEmail', 2, 'Email for orders', 'cible', '', 0, 14),
('list_column_CP_FreeItemID', 1, "Item gratuit", 'cible', '', 0, 14),
('list_column_CP_FreeItemID', 2, 'Free item', 'cible', '', 0, 14),
('list_column_CP_FreeMiniAmount', 1, "Montant minimum pour l'item gratuit", 'cible', '', 0, 14),
('list_column_CP_FreeMiniAmount', 2, 'Minimum amount for the item free', 'cible', '', 0, 14),
('list_column_CP_BonusPointDollar', 1, "Point bonis par dollar d'achat", 'cible', '', 0, 14),
('list_column_CP_BonusPointDollar', 2, 'Bonus Point per dollar', 'cible', '', 0, 14),
('list_column_CP_TauxTaxeFed', 1, "Taxe fédérale", 'cible', '', 0, 14),
('list_column_CP_TauxTaxeFed', 2, 'Federal tax', 'cible', '', 0, 14),
('management_module_catalog_parameters',1,"Paramètres de commande",'cible','', 0, 14),
('management_module_catalog_parameters',2,'Order parameters','cible','', 0, 14),
('list_column_parameters_taxe_label', 1, "Taxe fédérale", 'cible', '', 0, 14),
('list_column_parameters_taxe_label', 2, 'Federal tax', 'cible', '', 0, 14),
('management_module_catalog_items_promo', '1', 'Items en promotions', 'cible', '', 0, 14),
('management_module_catalog_items_promo', '2', 'Special offer', 'cible', '', 0, 14),
('form_label_cityTxt', 1, "Ville ", 'cible', '', 0, 14),
('form_label_cityTxt', 2, 'City ', 'cible', '', 0, 14),
('form_product_isnew_pro_label', 1, "Nouveauté : professionnels", 'cible', '', 0, 14),
('form_product_isnew_pro_label', 2, 'New : professionals', 'cible', '', 0, 14),
('form_item_display_label', 1, "Affiché seulement si connecté", 'cible', '', 0, 14),
('form_item_display_label', 2, 'Display only if logged', 'cible', '', 0, 14),
('form_item_noAdd_label', 1, "Ne peut pas être ajouté au panier", 'client', '', 0, 14),
('form_item_noAdd_label', 2, 'Not possible to add to cart', 'client', '', 0, 14),
('products_details_noAdd_cart', 1, "* Contactez-nous pour commander cet article.", 'client', '', 0, 14),
('products_details_noAdd_cart', 2, '* Contact us to order this item.', 'client', '', 0, 14),
('products_details_noAdd_cart_short', 1, "Contactez-nous *", 'client', '', 0, 14),
('products_details_noAdd_cart_short', 2, 'Contact us *', 'client', '', 0, 14),
('form_address_retailer_fr', 1, "Adresse en français", 'cible', '', 0, 14),
('form_address_retailer_fr', 2, 'Address in french', 'cible', '', 0, 14),
('form_address_retailer_en', 1, "Adresse en anglais", 'cible', '', 0, 14),
('form_address_retailer_en', 2, 'Address in english', 'cible', '', 0, 14),
('form_product_sequence_label', 1, "Ordre d'affichage", 'cible', '', 0, 14),
('form_product_sequence_label', 2, 'Sequence', 'cible', '', 0, 14),
('list_column_I_Seq', 1, "Ordre d'affichage", 'cible', '', 0, 14),
('list_column_I_Seq', 2, 'Sequence', 'cible', '', 0, 14);

REPLACE INTO Static_Texts (ST_Identifier, ST_LangID, ST_Value, ST_Type, ST_Desc_backend, ST_Editable, ST_ModuleID, ST_RichText) VALUES
('form_label_CCI_Name', 1, "Nom de la catégorie", "cible", "", 0, 14, 0),	('form_label_CCI_Name', 2, "Category name", "cible", "", 0, 14, 0),
('form_label_CC_ParentId', 1, "Rattachée à la catégorie", "cible", "", 0, 14, 0),	('form_label_CC_ParentId', 2, "Related to the category", "cible", "", 0, 14, 0),
('form_label_CC_Seq', 1, "Position", "cible", "", 0, 14, 0),	('form_label_CC_Seq', 2, "Position", "cible", "", 0, 14, 0),
('form_label_CCI_Description', 1, "Description de la catégorie", "cible", "", 0, 14, 0),	('form_label_CCI_Description', 2, "Category description", "cible", "", 0, 14, 0),
('form_label_PI_Name', 1, "Nom du produit", "cible", "", 0, 14, 0),	('form_label_PI_Name', 2, "Product name", "cible", "", 0, 14, 0),
('form_label_P_CategoryId', 1, "Catégorie", "cible", "", 0, 14, 0),	('form_label_P_CategoryId', 2, "Category", "cible", "", 0, 14, 0),
('form_label_P_Seq', 1, "Position", "cible", "", 0, 14, 0),	('form_label_P_Seq', 2, "Position", "cible", "", 0, 14, 0),
('form_label_PI_Description', 1, "Description du produit", "cible", "", 0, 14, 0),	('form_label_PI_Description', 2, "Product description", "cible", "", 0, 14, 0),
('form_label_PI_Options', 1, "Options", "cible", "", 0, 14, 0),	('form_label_PI_Options', 2, "Options", "cible", "", 0, 14, 0),
('form_label_PI_Notes', 1, "Notes", "cible", "", 0, 14, 0),	('form_label_PI_Notes', 2, "Notes", "cible", "", 0, 14, 0),
('header_delete_products_text', 1, "Suppression du produit", "cible", "", 0, 14, 0),	('header_delete_products_text', 2, "Delete product", "cible", "", 0, 14, 0),
('form_label_PI_Name_associate', 1, "Associer le(s) produit(s) suivant :", "cible", "", 0, 14, 0),
('form_label_PI_Name_associate', 2, "Associate following product(s)", "cible", "", 0, 14, 0),
('form_label_P_Number',1,'Code produit','cible','',0,14,0),
('form_label_P_Number',2,'Product code','cible','',0,14,0),
('form_label_I_ProductID', 1, "Associé au produit", "cible", "", 0, 14, 0),
('form_label_I_ProductID', 2, "Related to  the product", "cible", "", 0, 14, 0),
('form_label_I_Seq', 1, "Position", "cible", "", 0, 14, 0),
('form_label_I_Seq', 2, "Position", "cible", "", 0, 14, 0),
('form_label_II_Name', 1, "Nom de l'item", "cible", "", 0, 14, 0),
('form_label_II_Name', 2, "Item name", "cible", "", 0, 14, 0),
('form_label_I_Number', 1, "Code de l'item", "cible", "", 0, 14, 0),
('form_label_I_Number', 2, "Item code", "cible", "", 0, 14, 0);
