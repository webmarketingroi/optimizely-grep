<?php

namespace WebMarketingROI\OptimizelyGrep;

use WebMarketingROI\OptimizelyPHP\OptimizelyApiClient;

/**
 * This class is responsible for searching for a search string in an Optimizely project.
 * It looks for the search string inside project data, experiments, audiences,
 * campaigns, and attributes.
 */
class OptimizelyGrep
{
    /**
     * Optimizely client.
     * @var WebMarketingROI\OptimizelyPHP\OptimizelyApiClient
     */
    private $optimizelyClient;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        
    }
    
    /**
     * Runs the search. 
     */
    public function run($argc, $argv)
    {
        try {
            if ($argc != 3) {
                throw new \Exception('Unexpected argument count.');
            }
            
            // Extract arguments from command line.
            $projectId = $argv[1];
            $searchString = $argv[2];
            
            // Init Optimizely client
            $this->initOptimizelyClient();
        
            // Perform the search.
            $searchResult = $this->searchInProject($projectId, $searchString);
            
            // Print search results.
            echo "Found " . count($searchResult) . " match(es) of search string '" . $searchString . "' in Optimizely project " . $projectId . ".\n\n";
            
            $num = 1;
            
            foreach ($searchResult as $item) {
                
                echo " $num. ";
                echo $item[1] . "\n";
                echo $item[0] . "\n";
                echo "\n";
                
                $num++;
            }
        }
        catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            $this->printUsage();
        }
    }
    
    /**
     * Prints the usage information.
     */
    private function printUsage()
    {
        echo "Usage: php optimizely-grep.php <project_id> <text_to_search_for>\n";
    }
    
    /**
     * Inits the Optimizely client and requests for the personal access token if needed.
     */
    private function initOptimizelyClient()
    {
        $authCredentials = [];
        
        if (is_readable('./data/auth_credentials.json')) {
            $authCredentials = json_decode(file_get_contents('./data/auth_credentials.json'), true);
        } else {
            $accessToken = readline("Enter your Optimizely personal access token: ");
            $authCredentials = [
                'access_token' => $accessToken
            ];
            file_put_contents('./data/auth_credentials.json', json_encode($authCredentials));
            echo "Access token is saved to ./data/auth_credentials.json\n";
        }
        
        $this->optimizelyApiClient = new OptimizelyApiClient($authCredentials);
    }
    
    /**
     * Searches for the search string (regexp) in an Optimizely project with the given ID.
     */
    private function searchInProject($projectId, $searchString) 
    {
        // A variable for storing the search results.
        $searchResult = [];
        
        try {
            
            echo "Searching in project data...\n";
            
            $result = $this->optimizelyApiClient->projects()->get($projectId);

            $project = $result->getPayload()->toArray();
            
            $projectData = explode("\n", json_encode($project, JSON_PRETTY_PRINT));
            
            foreach ($projectData as $line) {
                $matched = preg_match_all('#^[^:]+:.*'.$searchString.'.*#i', $line, $matches);
                
                //print_r($matches);
                
                if ($matched) {
                    $searchResult[] = [
                        $matches[0][0],
                        "Found in project '$projectId' data in line '$line'"
                    ];
                }
            }

            //print_r($projectData);
            
            echo "Searching in experiments...\n";
            
            $page = 1;
            for (;;) {
    
                $result = $this->optimizelyApiClient->experiments()->listAll($projectId, null, true, $page, 25);
                $experiments = $result->getPayload();

                foreach ($experiments as $experiment) {
                    
                    echo " - searching in experiment '" . $experiment->getName() . "'\n"; 
                    
                    $experimentData = explode("\n", json_encode($experiment->toArray(), JSON_PRETTY_PRINT));

                    //print_r($experimentData);
                
                    foreach ($experimentData as $line) {
                        
                        $matched = preg_match_all('#^[^:]+:.*'.$searchString.'.*#i', $line, $matches);

                        //print_r($matches);

                        if ($matched) {
                            $searchResult[] = [
                                $matches[0][0],
                                "Found in experiment '" . $experiment->getName() . "' data in line '$line'"
                            ];
                        }
                    }
                } 
                
                // Determine if there are more experiments.
                if ($result->getNextPage()==null)
                    break;

                // Increment page counter.
                $page ++;
            }
            
            echo "Searching in audiences...\n";
            $page = 1;
            for (;;) {
    
                $result = $this->optimizelyApiClient->audiences()->listAll($projectId, $page, 25);
                $audiences = $result->getPayload();

                foreach ($audiences as $audience) {

                    echo " - searching in audience '" . $audience->getName() . "'\n"; 
                    
                    $audienceData = explode("\n", json_encode($audience->toArray(), JSON_PRETTY_PRINT));

                    //print_r($audienceData);

                    foreach ($audienceData as $line) {
                        
                        $matched = preg_match_all('#^[^:]+:.*'.$searchString.'.*#i', $line, $matches);

                        //print_r($matches);

                        if ($matched) {
                            $searchResult[] = [
                                $matches[0][0],
                                "Found in audience '" . $audience->getName() . "' data in line '$line'"
                            ];
                        }
                    }
                }
                
                // Determine if there are more audiences.
                if ($result->getNextPage()==null)
                    break;

                // Increment page counter.
                $page ++;
            } 
            
            
            echo "Searching in campaigns...\n";
            
            $page = 1;
            for (;;) {
    
                $result = $this->optimizelyApiClient->campaigns()->listAll($projectId, $page, 25);
                $campaigns = $result->getPayload();

                foreach ($campaigns as $campaign) {
                
                    echo " - searching in campaign '" . $campaign->getName() . "'\n"; 
                    
                    $campaignData = explode("\n", json_encode($campaign->toArray(), JSON_PRETTY_PRINT));

                    //print_r($campaignData);
                    
                    foreach ($campaignData as $line) {
                        
                        $matched = preg_match_all('#^[^:]+:.*'.$searchString.'.*#i', $line, $matches);

                        //print_r($matches);

                        if ($matched) {
                            $searchResult[] = [
                                $matches[0][0],
                                "Found in campaign '" . $audience->getName() . "' data in line '$line'"
                            ];
                        }
                    }
                }
                
                // Determine if there are more audiences.
                if ($result->getNextPage()==null)
                    break;

                // Increment page counter.
                $page ++;
            }
            
            echo "Searching in pages...\n";
            
            $page = 1;
            for (;;) {
    
                $result = $this->optimizelyApiClient->pages()->listAll($projectId, $page, 25);
                $pages = $result->getPayload();

                foreach ($pages as $pageEntity) {
                
                    echo " - searching in page '" . $pageEntity->getName() . "'\n"; 
                    
                    $pageData = explode("\n", json_encode($pageEntity->toArray(), JSON_PRETTY_PRINT));

                    //print_r($pageData);
                    
                    foreach ($pageData as $line) {
                        
                        $matched = preg_match_all('#^[^:]+:.*'.$searchString.'.*#i', $line, $matches);

                        //print_r($matches);

                        if ($matched) {
                            $searchResult[] = [
                                $matches[0][0],
                                "Found in page '" . $pageEntity->getName() . "' data in line '$line'"
                            ];
                        }
                    }
                }
                
                // Determine if there are more pages.
                if ($result->getNextPage()==null)
                    break;

                // Increment page counter.
                $page ++;
            }
            
            echo "Searching in attributes...\n";
            
            $page = 1;
            for (;;) {
    
                $result = $this->optimizelyApiClient->attributes()->listAll($projectId, $page, 25);
                $attributes = $result->getPayload();

                foreach ($attributes as $attribute) {
                
                    echo " - searching in attribute '" . $attribute->getName() . "'\n"; 
                    
                    $pageData = explode("\n", json_encode($attribute->toArray(), JSON_PRETTY_PRINT));

                    //print_r($pageData);
                    
                    foreach ($pageData as $line) {
                        
                        $matched = preg_match_all('#^[^:]+:.*'.$searchString.'.*#i', $line, $matches);

                        //print_r($matches);

                        if ($matched) {
                            $searchResult[] = [
                                $matches[0][0],
                                "Found in page '" . $attribute->getName() . "' data in line '$line'"
                            ];
                        }
                    }
                }
                
                // Determine if there are more pages.
                if ($result->getNextPage()==null)
                    break;

                // Increment page counter.
                $page ++;
            }            
        }
        catch (Exception $e) {
            echo "Optimizely REST API error: " . $e->getMessage() . "\n";
        }
        
        return $searchResult;
    }
}

