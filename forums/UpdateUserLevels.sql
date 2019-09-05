UPDATE users SET PrivelegeLevel=5 WHERE PrivelegeLevel=2;
UPDATE users SET PrivelegeLevel=4 WHERE PrivelegeLevel=1;
UPDATE users SET PrivelegeLevel=1 WHERE PrivelegeLevel=0;

INSERT INTO userlevels 
	(UserLevelID, Title)
VALUES
	(1, 'User'),
	(2, 'Super User'),
	(3, 'Forum Moderator'),
	(4, 'Site Admin'),
	(5, 'Maxcape Developer');