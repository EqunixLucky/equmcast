<?

include_once('htmlBuilder.php');

$h = new htmlBuilder("skeleton");

$h->buildStructure();

print_r($h->menuTree);
print_r($h->mainMenu);
print_r($h->pageMenu);
print_r($h->pageContent);

?>
