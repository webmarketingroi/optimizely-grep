# optimizely-grep

A simple PHP tool for searching for a string in an Optimizely project. It will search
in the project data, and in all experiments, audiences, attributes, campaigns and pages.

Basically, you run `optimizely-grep <projectid> "exclude the web page"` or something 
similar and it tells you where in that project (i.e.: which campaign(s), audience(s), 
etc) have that string "exclude the web page" or whatever else it might be.

`optimizely-grep` was developed by Optimizely Solutions Partner, Web Marketing ROI. 

## Installation

This tool requires at least version 5.6 of PHP with the `curl` extension installed.

Clone or download the code to some directory. Then install dependencies with Composer:

`php composer.phar update`

If everything OK, you should be able to run the tool:

`php optimizely-grep.php`

## Optimizely Authentication

Optimizely's REST API uses OAuth 2.0 for authentication. Therefore the 
`optimizely-grep` tool will prompt for a Optimizely's personal API token (see 
[this page](https://help.optimizely.com/Integrate_Other_Platforms/Generate_a_personal_access_token_in_Optimizely_X_Web) for instructions on how to get it).

## Searching

Type the following to search for the `search_string` in the Optimizely project with the 
`project_id`:

```
php optimizely-grep.php <project_id> <search_string>
``` 

This will output the list of matches.