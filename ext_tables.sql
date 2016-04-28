#
# Table structure for table 'tx_jobqueue_domain_model_failedjob'
#
CREATE TABLE tx_jobqueue_domain_model_failedjob (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	queue_name varchar(255) DEFAULT '' NOT NULL,
	payload text NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);
