
# Uk Mobile Validator

  


A PHP CLI tool to check if mobile phone numbers are from the UK and valid. Uses the https://www.hlr-lookups.com/ API - an account is required.

  

| Option | Description | Required |

|----------------|---------------------------------------|-----------------------------|

|`--user` | the username for hlr-lookups.com | True |

|`--pass` | the password for hlr-lookups.com | True |

|`--file` | the input file of phone numbers | True |

|`--output` | the output file of results (csv) | False |

  

If `--output` isn't specified then the output will be displayed as a table.


## Install

  

`composer install`

## example

  

`./index.php --user <user> --pass <pass> --file numbers.txt --output results.csv`
