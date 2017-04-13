# INTRODUCTION #

**Wamp** has a really fine user interface, but it's not always straightforward to understand how various Apache, MySql and PHP versions are or can be installed.

The **WampManager** class allows you to drive **Wamp** from within a script ; it provides the following features :

- Since Wamp does not write anything in the Windows registry, the **WampManager** class is able to detect if a Wamp installation resides in the root of any of your partitions (you can also define a **WAMPDIR** environment variable that points to the installation directory).
- It can retrieve all the Apache, MySql and PHP versions currently configured with your Wamp installation. 
- It gives the currently active Apache, MySql and PHP versions, and allows you to switch between existing versions.
- It allows you to start/stop/restart any or all of the running services
- It can restart **Wamp** if you changed settings in the configuration
- You can configure additional MySql services, using different my.ini files

Instantiating a **WampManager** object is fairly simple :

	include ( 'WampManager.phpclass' ) ;

	$wamp 	=  new WampManager ( ) ;

If the class finds a valid WAMP installation directoy on your system, it will return a valid instance ; otherwise, an exception will be thrown.

You will have access to properties such as *PhpPackages*, *MySqlPackages* and *ApachePackages* which are array objects giving you access to the settings of each package.

The *Php*, *MySql* and *Apache* properties will return you the currently active version of each product. 

You will also have access to the WAMP installation and logs directories through the *InstallationDirectory* and *LogsDirectory* properties. The *WampExecutable* property will give you the complete path of WAMPMANAGER.EXE.

The *WampManager::$WampManagerConfiguration* static property wraps the contents of the *WampManager.conf* .INI file located at the root of the installation directory and allows you to retrieve or change settings. 

# REFERENCE #

## WampManager class ##

The **WampManager** class is the top-level class to access the inner settings of your WAMP configuration, start and stop services, etc.

### Methods ###

#### Constructor ####

	 $wamp 	=  new WampManager ( ) ;

Creates a **WampManager** class instance and reads all the settings defined in your current installation. Note that *creating a WampManager instance* does not mean *launch WampManager*. At this stage, only an object is created, and it does not affect your running instance of **Wamp**.

The class searches the root of each of all your non-removable partitions to locate a directory that looks like "\*Wamp\*". Of course, if such a directory has been found, it effectively checks that it contains the traditional tree hierarchy of **Wamp** ; this means :

- A WAMPSERVER.EXE executable
- Various configuration files
- At least one version of the MySql, Apache and PHP packages with their corresponding executables and configuration files
- To be considered as configured for **Wamp**, each MySql/Apache/PHP directory must contain a *WampServer.conf* file.

You can override this behavior by defining the **WAMPDIR** environment variable to point to your Wamp installation directory.

#### ConfigurationChanged ####

	$wamp -> ConfigurationChanged ( ) ;

Call this method whenever you have made any changes to the *WampManager.conf* file through the *WampManager::$WampManagerConfiguration* object. It will save any changes performed so far and restart **Wamp**.

#### Restart ####

	$wamp -> Restart ( $verbose = false ) ;

Restarts all the running services (Apache and MySql) along with **Wamp** itself.

Debug messages will be displayed if the *$verbose* parameter is set to *true*.

Note that PHP itself is not to be considered as a service : it is a DLL used by Apache. 

#### SaveConfiguration ####

	$wamp -> SaveConfiguration ( ) ;

Saves the currently loaded **Wamp** configuration.

This method is a shortcut for :

	WampManager::$WampManagerConfiguration -> Save ( ) ;

#### Start ####

	$wamp -> Start ( $verbose = false ) ;

Starts all the **Wamp** services (Apache and MySql).

Debug messages will be displayed if the *$verbose* parameter is set to *true*. 

Note that PHP itself is not to be considered as a service : it is a DLL used by Apache. 

#### Stop ####

	$wamp -> Stop ( $verbose = false ) ;

Stops all the **Wamp** services (Apache and MySql).

Debug messages will be displayed if the *$verbose* parameter is set to *true*. 

Note that PHP itself is not to be considered as a service : it is a DLL used by Apache. 
	
### Properties ###

#### Apache ####

Returns the currently active Apache version (of type **ApachePackage**).

### ApachePackages ###

An array of type **ApachePackages** containing **ApachePackage** objects that give information about your currently installed Apache versions.

#### InstallationDirectory ####

Returns the directory of your current **Wamp** installation.

#### LogDirectory ####

Returns the logs directory of your current **Wamp** installation (normally, unless modified, this should be a directory named *logs* directly under the installation directory).

#### MySql ####

Returns the currently active MySql version (of type **MySqlPackage**).

#### MySqlPackages ####

An array of type **MySqlPackages** containing **MySqlPackage** objects that give information about your currently installed MySql versions.

#### Php ####

Returns the currently active PHP version (of type **PhpPackage**).

#### PhpPackages ####

An array of type **PhpPackages** containing **PhpPackage** objects that give information about your currently installed PHP versions.

#### WampExecutable ####

Returns the full path of WAMPMANAGER.EXE.

#### WampManagerConfiguration ####

This static property gives you access to your current **Wamp** settings, through an object of type **IniFile**. You can retrieve them and modify them.

You can find more information about the **IniFile** class here :

	[https://www.phpclasses.org/package/9413-PHP-Load-and-edit-configuration-INI-format-files.html](https://www.phpclasses.org/package/9413-PHP-Load-and-edit-configuration-INI-format-files.html "https://www.phpclasses.org/package/9413-PHP-Load-and-edit-configuration-INI-format-files.html")
 
## Packages collections (WampPackages classes)##

The **WampManager** package comes with 3 classes that represent a collection of installed Wamp packages :

- **ApachePackages**, which contain entries of type **ApachePackage**
- **MySqlPackages**, which contain entries of type **MySqlPackage**
- **PhpPackages**, which contain entries of type **PhpPackage**

Each collection inherits from the **WampPackages** file. They currently do not add any new behavior to their base class, but only have different constructor parameters.

The **ApachePackage** and **MySqlPackage** classes inherit from the **WampService** class ; the **PhpPackage** class inherits from **WampModule**.

Both **WampService** and **WampModule** classes inherit from **WampPackage**. 

The following sections describe the properties and methods of the **WampPackages** class, which apply to all of its derived classes. Since this class is mainly used internally, most of the properties and methods are given here for informational purposes only.

Note that this class implements the *ArrayAccess*, *Countable* and *Iterator* interface, so that you can loop through a package list as if it were an array.

### Methods ###

#### Constructor ####

	$Packages	=  new WampPackages ( $name, $class, $parent, $rootdir, $prefix, $active_version ) ;

Creates a list of Packages whose configuration information is to be loaded.

Since this is an abstract class, it is the responsibility of derived classes (PhpPackages, MySqlPackages
and ApachePackages) to provide the required parameters appropriate to their function.

The parameters are the following :

- **$name** *(string)* : Official list name ("PHP", "MySQL", "Apache").
- **$class** *(string)* :  Name of class deriving from WampPackage which is responsible for loading package data.
- **parent** *(WampManager object)* : Parent **WampManager** object.
- **rootdir** *(string)* : Base directory under Wamp root (eg, 'bin/php').
- **prefix** *(string)* : Prefix string used to name directories under $rootdir. For example, it will be 'php' for PHP versions (they all start with the string 'php'), 'mysql' for mysql version, 'apache' for apache versions.
- **active\_version** *(string)* : The derived class must retrieve the currently active version from *wampmanager.conf*. It will be 'phpCliVersion' in the [phpCli] section, 'apacheVersion' in the [apache] section, and 'mysqlVersion' in the [mysql] version.

Note that the documentation about this constructor is for informational purposes only, since it is used internally by the **WampManager** class.


#### GetActivePackage ####

	$package 	=  $packages -> GetActivePackage ( ) ;

Returns the object corresponding to the currently active version of the related product (**PhpPackage** for PHP, **MySqlPackage** for Mysql and **ApachePackage** for Apache.

A typical usage could be :

	$package 	=  $wamp -> MySqlPackages -> GetActivePackage ( ) ;

which is also equivalent to :

	$package 	=  $wamp -> MySql ;

#### GetActiveVersion ####

	$version 	=  $packages -> GetActiveVersion ( ) ;

Returns the active version as a string for the related product. A typical usage could be :

	$version 	=  $wamp -> PhpPackages -> GetActiveVersion ( ) ;


#### GetVersions ####

	$list 		=  $packages -> GetVersions ( ) ;

Returns a list of versions for the related product ; the following example retrieves the currently installed PHP versions :

	$versions 	=  $wamp -> PhpPackages -> GetVersions ( ) ;

#### SetVersion ####

	$status 	=  $packages -> SetVersion ( $version, $reload = true ) ;

Sets the currently active version. If the *$reload* parameter is *false*, the change will be effective after restarting WAMP.

#### VersionExists ####

	$status 	=  $packages -> VersionExists ( $version ) ;

Checks if the specified version exists. This is equivalent to :

	$status 	=  isset ( $packages [ $version ] ) ;

### Properties ###

#### Name ####

Returns the underlying name of the package collection : either "PHP", "MySql" or "Apache".


## Package classes ##

Package classes are objects contained in a package list ; they have the following hierarchy :

						                WampPackage
				                             |
				                        WampModule
				                             |
				             +---------------+--------------+
				             |                              |
				       WampService                     PhpPackage
				             |
			 +---------------+--------------+
			 |                              |
		ApachePackage                 MySqlPackage

**WampPackage** is the abstract base class for all WAMP modules (Apache, MySql and Php). The **WampModule** class further defines the behavior of an installed module. Then comes a fork in the hierarchy :

- **PhpPackage** classes directly inherit from **WampModule**
- The **ApachePackage** and **MySqlPackage** classes inherit from the **WampService** class, which is a descendant of **WampModule** that provides additional methods specific to Windows services.

The following sections describe the methods and properties exposed by each class.

## Package class : WampPackage ##

The **WampPackage** is the abstract base class for all WAMP modules.

### Methods ###

#### Constructor ####

	$package 	=  new WampPackage ( $name, $parent, $path, $version, $exedir, $exefile, $confdir, $conffile ) ;

Instantiates a **WampPackage** object ; the parameters are the following :

- **$name** *(string)* : Name of the package (PHP, MySql or Apache)
- **$parent** *(WampManager object)* : Parent WampManager object.
- **path** *(string)* : Path, in the WAMP installation directory, of the specified package (eg, C:\Wamp\bin\php\php5.6.12)
- **version** *(string)* : Version string
- **exedir** *(string)* : Directory containing the package executable, which can be a subdirectory of *$path*.
- **$exefile** *(string)* : Full executable path.
- **confdir** *(string)* : Directory containing the configuration file.
- **conffile** *(string)* : Full configuration file path.

Note : this constructor is used internally. It is not meant to be called from within a script.

#### GetConfigurationFiles ####

	$files 	=  $package -> GetConfigurationFiles ( ) ;

This abstract function is meant to return the paths of all the configuration files handled by the underlying package.


#### GetLogFiles ####

	$files 	=  $package -> GetLogFiles ( ) ;

This abstract function is meant to return the paths of all the log files handled by the underlying package.

### Properties ###

#### ConfigurationDirectory ####

Returns the path of the configuration directory for the underlying package.

#### ConfigurationFile ####

Returns the path of the main configuration file for the underlying package.

#### ExecutableFile ####

Returns the path of the executable file for the underlying package.

#### Name ####

Returns the name of the underlying package (PHP, MySql or Apache).

#### Path ####

Returns the installation path of the underlying package.


#### Version ####

Returns the package version string.

## Package class : WampModule ##

This class extends the **WampPackage** class by adding the following methods :

### IsRunning ###

	$status 	=  $package -> IsRunning ( ) ;

Checks if the specified module is currently running.

### Restart ###

	$package -> Restart ( ) ;

Restarts the specified module or service.

### Start ###

	$package -> Start ( ) ;

Starts the specified module or service.

### Stop ###

	$package -> Stop ( ) ;

Stops the specified module or service.


## Package class : WampService ##

This class extends the **WampPackage** class by adding the methods described below ; note that the Install/Uninstall methods are not yet implemented to create/remove new httpd services and will throw an exception.

### Install ###

	$package -> Install ( $service_name, $configuration_file ) ;

Installs a new instance (Windows service) of the underlying package.

### Uninstall ###

	$package -> Uninstall ( $service_name ) ;

Uninstalls a existing WAMP Windows service.


## Package class : ApachePackage ##

This class extends the **WampService** class by adding the following methods :

### GetModules ###

	$list 	=  $package -> GetModules ( ) ;

Returns the list of currenly declared Apache modules.

## Package class : MySqlPackage ##

This class can install additional Windows MySql services or remove them. Note that in the supplied configuration file, the main section name defining the host port, the database directory, etc. must match the service name you supply to the *Install()** method.

For example, WAMP 64-bits has a section named [wampmysqld64] in its *my.ini* file.  If you want to install a service named "foobar", then the configuration file you specified will need to have a [foobar] section for defining MySql parameters.

A gentle reminder :

- You cannot start a second MySql service on the same port that the currently running one
- You cannot start a second MySql service using the same database directory as the existing one 


## Package class : PhpPackage ##

This class extends the **WampService** class by adding the following methods :

### GetModules ###

	$list 	=  $package -> GetModules ( ) ;

Returns the list of currenly declared PHP modules.