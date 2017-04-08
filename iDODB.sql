/* iDO database tables*/

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+02:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-----------------------------------------------------------
/* Users table */
DROP TABLE IF EXISTS Users;
CREATE TABLE IF NOT EXISTS Users (  
	ID VARCHAR(50) NOT NULL,
	Name VARCHAR(50) NOT NULL,
	Email VARCHAR(50) NOT NULL,
	Phone VARCHAR(12)  DEFAULT NULL,
	Event1 INT  DEFAULT NULL,
	Permission1 VARCHAR(50) DEFAULT NULL,
	Event2 INT DEFAULT NULL,
	Permission2 VARCHAR(50) DEFAULT NULL,
	Event3 INT DEFAULT NULL,
	Permission3 VARCHAR(50) DEFAULT NULL
    ) DEFAULT CHARACTER SET utf8; 

/* demo users */
INSERT INTO Users (ID, Name, Email, Phone, Event1, Permission1, Event2, Permission2, Event3, Permission3) VALUES
	('12345678', 'Dan', 'Dan@gmail.com', '054-1231234', '1', 'root', NULL, NULL, NULL, NULL),
	('87654321', 'Dana', 'Dana@gmail.com', '052-2222222', '2', 'edit', NULL, NULL, NULL, NULL),
	('1111', 'Yosi', 'yos@gmail.com', '055-5555555', '2', 'root', NULL, NULL, NULL, NULL),
	('22', 'Yosefa', 'efa@gmail.com', '053-33333333', '1', 'review', NULL, NULL, NULL, NULL);


-----------------------------------------------------------
/* Events table */
DROP TABLE IF EXISTS Events;
CREATE TABLE IF NOT EXISTS Events (
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	EventName  VARCHAR(50) NOT NULL,
	EventDate DATE NOT NULL,
  HebrewDate VARCHAR(50) DEFAULT NULL,
        EventTime TIME DEFAULT NULL,
        Venue VARCHAR(50) DEFAULT NULL,
        Address VARCHAR(75) DEFAULT NULL,
        RootID VARCHAR(50) NOT NULL,
        Email VARCHAR(50) DEFAULT NULL,
	Phone VARCHAR(12) DEFAULT NULL,
        Password VARCHAR(50) DEFAULT NULL,
        Secret VARCHAR(50) DEFAULT NULL,
        DeviceID VARCHAR(50) DEFAULT NULL,
        Created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
	) DEFAULT CHARACTER SET utf8; 

/* demo events */
INSERT INTO Events (ID, EventName, EventDate, HebrewDate, EventTime, Venue, Address, RootID, Email, Phone, Password, Secret, DeviceID, Created) VALUES
	(1, 'Dan and moshe', "2009-09-24", 'Tu Beav', '18:00', 'אולמי הנסיכה', '31 אורכידאה','12345678', 'Dan@gmail.com', '051-1111111', 'Bil123','79fhdasfAA', '76851Ad' ,"2008-09-24"),
	(2, 'Dana and Yosi', "2010-10-10", 'Rosh Hashana', '19:00','מוסקט','פתח תקווה','1111', 'yos@gmail.com', '0522222222', 'cannotLie', 'iLikeBigButs', '3hgfskdu34', "2009-10-10");

-----------------------------------------------------------

/* RSVP table */
DROP TABLE IF EXISTS RSVP1;
CREATE TABLE IF NOT EXISTS RSVP1 (
  ID INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(50) NOT NULL,
  Surname VARCHAR(50) NOT NULL,
  Nickname VARCHAR(50) DEFAULT NULL,
  Invitees INT(3) NOT NULL,
  Phone VARCHAR(12) DEFAULT NULL,
  Email VARCHAR(50) DEFAULT NULL,
  Groups VARCHAR(50) DEFAULT NULL,
  RSVP INT(3) DEFAULT NULL,
  Uncertin int(3) DEFAULT NULL,
  Ride BOOLEAN DEFAULT FALSE
) DEFAULT CHARACTER SET utf8;

DROP TABLE IF EXISTS RSVP2;
CREATE TABLE RSVP2 AS SELECT * FROM RSVP1;

DROP TABLE IF EXISTS RSVPsample;
CREATE TABLE RSVPsample AS SELECT * FROM RSVP1;

/* demo RSVP */
INSERT INTO RSVP1 (ID, Name, Surname, Nickname, Invitees, Phone, Email, Groups, RSVP, Uncertin, Ride) VALUES
	(1, 'Hoffman', 'Tatyana', 'Tat', 3, '054-1111111', 'Nam@quisdiamluctus.org', 'Friends', NULL, 2, False),
	(2, 'Atkins', 'Ishmael', NULL, 2, NULL, NULL, NULL, NULL, 0, False),
	(3, 'Hamilton', 'Mohammad', NULL, 22, '051-1111111', 'dui@duiCras.edu', 'Coligues', 11, 3, True),
	(4, 'Murray', 'Troy', 'the boy', 4, '055-5555555', 'mollis@eutellus.co.uk', NULL, NULL, 0, False),
	(5, 'Schwartz', 'Carla', NULL, 6, '056-6666666', NULL, NULL, 3, 0, True);

INSERT INTO RSVP2 (ID, Name, Surname, Nickname, Invitees, Phone, Email, Groups, RSVP, Uncertin, Ride) VALUES
	(1, 'Wilder', 'Chadwick', NULL, 3, NULL, NULL, NULL, NULL, False),
	(2, 'Hardin', 'Audrey', 'DJ', 5, '000-0000000', 'commodo.tincidunt.nibh@seddictum.com', 'Friends', 7, 2, True),
	(3, 'Vaughn', 'Harlan', 'Bijo', 1, NULL , 'tempor.lorem@egestasDuisac.com', 'Tachat', 0, 0, False),
	(4, 'Mathis', 'Lareina', NULL , 1, '111111111', 'ac.turpis.egestas@Proin.com', 'Friends', 1, 1, True),
	(5, 'Barrera', 'Chiquita', 'Chiquita', 8, '099-985637932', 'ipsum@tempus.org', 'Friends', 10, 2, False);

INSERT INTO RSVPsample (Name, Surname, Nickname, Invitees, Phone, Email, Groups, RSVP, Ride) VALUES
	('John', 'Do', 'Johany', '3', '051-1111111', 'JohanDo@gmail.com', 'friends', '2', 'yes');

-----------------------------------------------------------
/* Message table */
DROP TABLE IF EXISTS Messages1;
CREATE TABLE IF NOT EXISTS Messages1 (
  ID INT(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  MessageType VARCHAR(10) NOT NULL,
  Message TEXT NOT NULL,
  Groups VARCHAR(100) DEFAULT NULL,
  SendDate DATE NOT NULL,
  SendTime TIME NOT NULL
) DEFAULT CHARACTER SET utf8;

DROP TABLE IF EXISTS Messages2;
CREATE TABLE Messages2 AS SELECT * FROM Messages1;

/* demo Messages */
INSERT INTO Messages1 (ID, MessageType, Message, Groups, SendDate, SendTime) VALUES
	(1, 'SaveTheDate', 'Come celebrate with us!', 'Friends, Family, Army', '2009-06-24', '10:10:00'  ),
	(2, 'ThankYou', 'you are AWSOME!', 'Family', '2009-09-25', '12:00:00');

INSERT INTO Messages2 (ID, MessageType, Message, Groups, SendDate, SendTime) VALUES
	(1, 'SaveTheDate', 'Come celebrate with us!', NULL, '2009-10-10', '05:10:00'),
	(2, 'ThankYou', 'you are AWSOME!', NULL, '2009-09-25', '06:23:12');

-----------------------------------------------------------
/* Message RawData */
DROP TABLE IF EXISTS RawData1;
CREATE TABLE IF NOT EXISTS RawData1 (
	ID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	Name VARCHAR(50) NOT NULL,
	Surname VARCHAR(50) NOT NULL,
	Phone VARCHAR(12) DEFAULT NULL,
	Email VARCHAR(50) DEFAULT NULL,
	Groups VARCHAR(50) DEFAULT NULL,
	RSVP INT(3) DEFAULT NULL,
        Ride BOOLEAN DEFAULT FALSE,
	Message TEXT NOT NULL,
        Recived datetime NOT NULL DEFAULT CURRENT_TIMESTAMP  
) DEFAULT CHARACTER SET utf8;

DROP TABLE IF EXISTS RawData2;
CREATE TABLE RawData2 AS SELECT * FROM RawData1;


/* demo RawData */
INSERT INTO RawData1 (ID, Name, Surname, Phone, Email, Groups, RSVP, Message, Recived) VALUES
	(1, 'Hoffman', 'Tatyana','054-1111111', 'Nam@quisdiamluctus.org', 'Friends', 5, 'we are 5 people', '2009-10-10 10:10:10'),
	(2, 'Hamilton', 'Mohammad', '051-2222222', 'dui@duiCras.edu', 'Coligues', 11, '11', '2009-12-12 12:12:00');

INSERT INTO RawData2 (ID, Name, Surname, Phone, Email, Groups, RSVP, Message, Recived) VALUES
	(1, 'Oriah', 'Halamish','054-4444444', 'Nam@quisdiamluctus.org', 'Friends', 1, 'I will come solo', '2009-10-10 10:10:10'),
	(2, 'Gil', 'levy', '051-1111111', 'dui@duiCras.edu', NULL, 2, 'two people', '2009-12-12 12:12:00');




COLLATE utf8_General_ci;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
