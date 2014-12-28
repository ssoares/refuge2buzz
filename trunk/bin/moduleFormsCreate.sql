-- phpMyAdmin SQL Dump
-- version 3.1.3
-- http://www.phpmyadmin.net
--
-- Serveur: 209.222.235.12:3306
-- Version SVN: $Id: moduleFormsCreate.sql 1367 2013-12-27 04:19:31Z ssoares $

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

INSERT INTO `Modules` (`M_ID`, `M_Title`, `M_MVCModuleTitle`) VALUES
(11, 'Forms', 'forms');

INSERT INTO ModuleViews (MV_ID, MV_Name, MV_ModuleID) VALUES
(11001, 'forms_contact', 11);

INSERT INTO ModuleViewsIndex (MVI_ModuleViewsID, MVI_LanguageID, MVI_ActionName) VALUES
(11001, 1,'forms-contact'),
(11001, 2,'forms-contact');

REPLACE INTO `NotificationManagerData` (`NM_ID`, `NM_ModuleId`, `NM_Event`, `NM_Type`, `NM_Recipient`, `NM_Active`, `NM_Message`, `NM_Title`, `NM_Email`) VALUES
(11, 11, 'contact', 'email', 'admin', 1, 'contact_form_notification_admin_message', 'contact_form_notification_admin_title', 'no-reply@domainname.com');

REPLACE INTO `Static_Texts` (`ST_Identifier`, `ST_LangID`, `ST_Value`, `ST_Type`, `ST_Desc_backend`, `ST_Editable`, `ST_ModuleID`, `ST_ModifDate`) VALUES
('contact_form_notification_admin_message', 1, "Un message vous a été envoyé via votre site Internet ##siteDomain##<br /><br />
<b>De:</b><br />##firstName## ##lastName##<br /><br />
<b>Courriel:</b> <br />##email##<br /><br />
<b>Message: </b><br />##comments##<br /><br />", 'client', 'Message notification admin: Formulaire de contact', '1', '11', CURRENT_TIMESTAMP),
('contact_form_notification_admin_message', 2, "A new message from your website ##siteDomain## has been posted.<br /><br />
<b>From:</b><br />##firstName## ##lastName##<br /><br />
<b>Email:</b> <br />##email##<br /><br />
<b>Message: </b><br />##comments##<br /><br />", 'client', 'Notification message admin: contact form', '1', '11', CURRENT_TIMESTAMP),
('contact_form_notification_admin_title', '1', 'Nous joindre', 'client', 'Message notification admin: Formulaire de contact (titre)', '1', '11', CURRENT_TIMESTAMP),
('contact_form_notification_admin_title', '2', 'Contact us', 'client', 'Notification message admin: contact form (title)', '1', '11', CURRENT_TIMESTAMP),
('forms_label_name', '1', 'Nom', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('forms_label_name', '2', 'Last name', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('form_label_comments', '1', 'Commentaires', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('form_label_comments', '2', 'Comments', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('message_contact_succeed', '1', 'Votre message a été envoyé! Merci de votre intérêt.', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('message_contact_succeed', '2', 'Your message has been sent! Thank you for your interest.', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('newsletter_captcha_label', '1', '<br /><br />Pour des raisons de sécurité, veuillez entrer les caractères alphanumériques de l''image dans l''espace ci-dessous..', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('newsletter_captcha_label', '2', '<br /><br />For security reasons, please enter the alphanumeric <br />characters from the image into the space below.', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('button_captcha_refresh', '1', 'Recharger l''image', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('button_captcha_refresh', '2', 'Reload image', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('forms_label_surname', '1', 'Prénom', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('forms_label_surname', '2', 'First name', 'cible', '', '0', '11', CURRENT_TIMESTAMP),
('formSentCode', 1, 'message-envoye', 'cible', '', 0, '11', CURRENT_TIMESTAMP),
('formSentCode', 2, 'message-sent', 'cible', '', 0, '11', CURRENT_TIMESTAMP);


