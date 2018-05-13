--snake_game Databasens tabeller

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `highscores` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `userid` int(11) NOT NULL,
  `score` int(11),
  `score_time` datetime NOT NULL,
  FOREIGN KEY (userid) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;