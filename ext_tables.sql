#
# Table structure for table 'tx_jobqueue_domain_model_job'
#
CREATE TABLE tx_jobqueue_domain_model_job (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	queue_name varchar(255) DEFAULT '' NOT NULL,
	payload text NOT NULL,
	state int(11) DEFAULT '0' NOT NULL,
	attemps int(11) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);
