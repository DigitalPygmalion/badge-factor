# Badge Factor

## What is Badge-Factor?

Badge Factor is a "glue" plugin which brings together many different plugins in order to deliver a comprehensive open badge solution.

## Installation and requirements 

You can clone the the project from this repository and copy it into the plugins folder of your wordpress project. 
To use this plugin you will need some additional plugin that are essentiel to the use of badgefactor.

Here is the list of plugins needed and the links to their repository or project page:

- BadgeOS : [badgeos][1]
- Buddypress : [buddypress][2]
- GravityForms : [gravityforms][3]
- Advanced custom fields : [ACF][4]

## Structure

    Badgefactor
    |-languages ( Only french translations for the moment)
    |-templates
        |-archive-organisation.php
        |-single-organistation.php
    |-widgets
    |-badgefactor.php (main file)
    |-composer.json
    |-LICENCE
    |-ReadMe.md
    |-settings-page.tpl.php

## Functionnality

The main functions of the plugin are located in the main file of the plugin.
On activation, an object of the class BadgeFactor is saved in a global variable in order to be accessible form any file in your website.

    $GLOBALS['badgefactor'] = new BadgeFactor();

[1]: https://github.com/opencredit/badgeos
[2]: https://github.com/buddypress/BuddyPress
[3]: http://www.gravityforms.com/
[4]: https://www.advancedcustomfields.com/
