-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Сен 05 2012 г., 06:18
-- Версия сервера: 5.5.24
-- Версия PHP: 5.3.10-1ubuntu3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `blog_system`
--
CREATE DATABASE `blog_system` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `blog_system`;

-- --------------------------------------------------------

--
-- Структура таблицы `articles`
--

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID of article',
  `author_id` int(11) NOT NULL COMMENT 'Author''s ID',
  `name` char(64) NOT NULL COMMENT 'Article''s name',
  `preview` char(128) NOT NULL COMMENT 'Article''s preview',
  `full` text NOT NULL COMMENT 'Article''s text',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date of creating article',
  `category11` int(11) NOT NULL,
  `category12` int(11) DEFAULT NULL,
  `category13` int(11) DEFAULT NULL,
  `category2` int(11) NOT NULL COMMENT 'Category from 2nd list',
  `good_voices` int(11) NOT NULL DEFAULT '0' COMMENT 'Number of good voices',
  `bad_voices` int(11) NOT NULL DEFAULT '0' COMMENT 'Number of bad voices',
  `confirmed` tinyint(1) NOT NULL COMMENT 'Is article confirmed',
  `judged` tinyint(1) NOT NULL COMMENT 'Is article judged',
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`,`category2`,`confirmed`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Table with articles' AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `articles`
--

INSERT INTO `articles` (`id`, `author_id`, `name`, `preview`, `full`, `date`, `category11`, `category12`, `category13`, `category2`, `good_voices`, `bad_voices`, `confirmed`, `judged`) VALUES
(1, 2, 'Первая статья', 'Эту статью надо написать, чтобы можно было спокойно продолжить', ' разработку. Я опять переустановил ось, но не подумал о дампе БД. Надо как-то сделать что ли автоматический бекап БД регулярный...\nP.S.: ЗАВТРА ДЕДЛАЙН!!! 5.9 - ТОТ САМЫЙ ДЕНЬ!!!!!!!!!!!11', '2009-12-31 21:00:13', 3, 0, 0, 4, 0, 0, 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID of category',
  `name` char(100) NOT NULL COMMENT 'Category''s name',
  `parent` int(11) NOT NULL COMMENT 'Parent category''s ID',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent`) VALUES
(1, 'list1', 0),
(2, 'list2', 0),
(3, 'Рубрика 1', 1),
(4, 'Рубрика 2', 2);

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID of comment',
  `author_id` int(11) NOT NULL COMMENT 'Author''s ID',
  `author_registered` tinyint(1) NOT NULL,
  `article_id` int(11) NOT NULL COMMENT 'Article''s ID',
  `text` char(255) NOT NULL COMMENT 'Text of comment',
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`,`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table with comments' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `complaints`
--

CREATE TABLE IF NOT EXISTS `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID of complaint',
  `article_id` int(11) NOT NULL COMMENT 'ID of article',
  `author_name` char(100) NOT NULL COMMENT 'Author''s name',
  `email` char(100) NOT NULL COMMENT 'Author''s email',
  `text` char(255) NOT NULL COMMENT 'Complaint text',
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `unregistered_commentors`
--

CREATE TABLE IF NOT EXISTS `unregistered_commentors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(16) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID of user',
  `email` char(150) NOT NULL COMMENT 'User''s email is used for authorizing',
  `password` tinytext NOT NULL COMMENT 'User''s password',
  `nickname` char(16) NOT NULL COMMENT 'User''s nickname',
  `group` char(50) NOT NULL DEFAULT 'user' COMMENT 'Rights group',
  `blocked_until` datetime DEFAULT NULL COMMENT 'If user is blocked, here should be date of unblocking',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `nickname`, `group`, `blocked_until`) VALUES
(2, 'andreykamozin@gmail.com', '1611d15f53a43081aff5afa84b7ee948', 'SkaN', 'user', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `voters`
--

CREATE TABLE IF NOT EXISTS `voters` (
  `user_id` int(11) NOT NULL COMMENT 'ID of voter',
  `article_id` int(11) NOT NULL COMMENT 'ID of article',
  `voice` char(4) NOT NULL COMMENT 'Voice'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
