# QMAN Datagrid
tato třída slouží k tabulkovému výpisu dat z aplikace QMAN.

Předpokládá existující registr v QMANovi, jehož sloupce jsou se stejným prefixem uloženy v evidenci COMMON v registru 
AppHashes a to způsobem že Id registru je uloženo jako <prefix>RegisterId, dále jsou tam jednotlivé sloupce.

Dále se předpokládá exitence modelu QMANService a entity, která má stejné property resp. gettry jako jsou jména sloupců
v AppHashes. Tedy pokud mám např. register Companies s Id 335, bude v AppHashes uložen např. s prefixem companies a bude 
evidován nazev firmy a IČO, bude v AppHashes uloženo companiesRegisterid, companiesTitle a companiesIco
s příslušnýmy hodnotami, pak by měla existovat entita Company s metodamy getTitle() a getIco() a také getId() a 
getUniqueHash()

V QMANservice pak zřídíme metodu:
```
/**
* @param int $limit
* @param int $offset
* @param array $filters
* @return CompanyEntity[]
* @throws \Throwable
*/
public function getCompanies(array $filters): array
{
$serializedFilters = Json::encode($filters);

		$key = "companies-all-{$this->getAppCompany()->getPointer()}-{$serializedFilters}";
		return $this->cache->load($key, function () use ($key, $filters) {
			try {
				$response = $this->post("workspace/{$this->getAppCompany()->getWorkspace()}/registeritem/search", [
					'query' => $filters['query'],
					'json' => [
						'registerdetailFilterElements' => $filters['filterElements'],
					],
				]);
				$response = JSON::decode((string)$response);

				if ($response->status === 'error') {
					Debugger::log($response);
					throw new CompanyServiceException($response->message);
				}
			} catch (CurlClientException|JsonException $e) {
				Debugger::log($e);
				throw new CompanyServiceException($e->getMessage());
			}

			$companies = [];

			foreach ($response->data->registeritems as $id => $value) {
				$rec = $response->data->datasetResult->values->{$value->id};
				$companies [] = $this->setCompany($value->id, $value->uniquehash, $rec);
			}

			return ['meta' => ['count' => $response->data->totalCount], 'data' => $companies];
		}, [
			Cache::Tags => [$this->getAppCompany()->getPointer()]
		]);
	}

	/**
	 * @param $response
	 * @return CompanyEntity
	 */
	protected function setCompany(int $companyId, string $hash, \stdClass $rec): CompanyEntity
	{
		$company = new CompanyEntity();
		$company->setId($companyId)
			->setTitle($rec->{$this->getAppHashes()->companiesTitle})
			->setName($rec->{$this->getAppHashes()->companiesName})
			->setStreet($rec->{$this->getAppHashes()->companiesStreet})
			->setCity($rec->{$this->getAppHashes()->companiesCity})
			->setZip($rec->{$this->getAppHashes()->companiesZip})
			->setIco($rec->{$this->getAppHashes()->companiesIco})
			->setDic($rec->{$this->getAppHashes()->companiesDic})
			->setBankAccount($rec->{$this->getAppHashes()->companiesBankAccount})
			->setUniqueHash($hash)
		;
		return $company;
	}
```

A poté můžeme přistoupit k vytvoření datagridu v presenteru, to uděláme tak, že si injecteme QMANDatagridFactory a vytvoříme componentu:
```
<?php

declare(strict_types=1);

namespace App\Modules\App\Main\Companies;

use App\Libs\Components\QMANDatagrid\QMANDatagrid;
use App\Libs\Components\QMANDatagrid\QMANDatagridFactory;

class ListCompaniesPresenter extends \App\Modules\App\Main\BasePresenter
{
	public function __construct(
		private readonly QMANDatagridFactory $datagridFactory,
	)
	{
	}

	protected function createComponentCompaniesAll(): QMANDatagrid
	{
		return $this->datagridFactory->create(
			'companiesAll',	'companies', 'getCompanies',[
				'columns' => [
					'title' => [
						'title' => 'Název',
					],
					'ico' => [
						'title' => 'IČ',
					],
				],
				'actions' => [
					'view' => [
						'title' => 'zobrazit',
						'link' => ':App:Main:Companies:Company:show',
					],
					'edit' => [
						'title' => 'editovat',
						'link' => ':App:Main:Companies:EditCompany:edit',
					],
				],
				'newRecord' => [
					'title' => 'Nová firma',
					'link' => ':App:Main:Companies:AddCompany:add',
				]
			]
		);
	}
}
```
jak vidíte výše metoda create bere čtyři parametry, první je jedinečné Id používané v html a javascriptu, druhý je prefix
použitý v registru AppHashes a poslední je jméno metody v QMANService. Posledním parametrem je pole s definicí datagridu.

Mělo by obsahovat položku `columns`, ve které jsou definovány příslušné sloupce s tím, že příslušný index sloupce musí 
odpovídat jméne property resp. getteru bez get v entitě. 

Pro jednotlivé columny musíme definovat `title`, což je název sloupce v hlavičce datagridu, dále je možné definovat `type`, 
který je defaultně `text`, ale může dále zatím nabývat hodnot: `text, date, datetime, radio, relationSelect, select, none`, 
tyto hodnoty určují jaký filter bude sloupec mít a jakým způsobem se bude vykreslovat. Pro `relationSelect` nebo `select` 
je třeba doplnit `options` pole, tedy id=>title seznam hodnot, kterých může select ve filtru a zároveň vykreslovaná hodnota 
nabývat. Pokud nechceme žádný filter, vložíme do `type` hodnotu `none`. Další je možnost nadefinovat zda má mít příslušný
sloupec řazení, to ovládáme pomocí `sorting`, přičemž výchozí hodnota je `true` a řazení se tedy jen 
případně vypíná pomocí `false`.

Další možností datagridu je možnost definovat akce, tedy poslední slopec, který bude automaticky přidaný a může obsahovat
akce, do kterých je automaticky do `linku` přidáno id příslušného záznamu resp. jeho `uniqueHash`. Akce má tedy index, 
v němž je jméno akce, které se vykreslí jako class u příslušného odkazu, a to ukazuje na pole, které má definovou položku
`title` a `link` viz ukázka výše. Dále muže mít položku `args`, pole jehož pomocí můžeme předat další parametry do 
vygenerovaného odkazu.

Datagrid umožňuje také vygenerování buttonu "Nový" pod položkou `newRecord`. Stejně jako u akcí definujeme `title` a `link` 
a samozřejmě můžeme přidat `args`.

Další možností datagridu je podpora příznaku `deleted`, který musí být v QMANovi resp. registru AppHashes zaveden jako 
<prefix>Deleted a očekává se, že půjde o přepínač v příslušném registeru. Může odsahovat hodnoty `true` a `false`, pokud 
není uveden, tak se vůbec nezohledňuje.

Zatím poslední možností je definovat přidané podmínky k dotazu v QKANService, to se hodí zejména pro výpis dat, které např.
patří konkrétnímu uživateli apod. Je to pole polí, kde jsou jednotlivé podmínky. 

Podívejte se na následující příklad:
```
public function createComponentDocumentAdditions(): QMANDatagrid
{
	$hash = $this->getParameter('id');
	$document = $this->QMANService->getDocument($hash);
	return $this->datagridFactory->create(
			'documentAdditions'.$hash,
			'documentsAdditions',
			'getDocumentAdditions',
			[
				'conditions' => [
					[
						'column' => 'documentId',
						'op' => 'include',
						'val' => [$document->getId()]
					]
				],
				'columns' => [
					'title' => [
						'title' => 'Název',
					],
				'number' => [
					'title' => 'Číslo dodatku',
				],
				'createdAt' => [
					'title' => 'Založen dne',
					'type' => 'datetime',
				],
				'approvedDay' => [
					'title' => 'Schválen dne',
					'type' => 'date',
				],
				'signDay' => [
					'title' => 'Podepsán dne',
					'type' => 'date',
				],
				'createdById' => [
					'title' => 'Založil',
					'type' => 'relationSelect',
					'options' => $this->QMANService->getNumberBookUsers(),
					'sorting' => false,
				],
			],
			'actions' => [
				'view' => [
					'title' => 'zobrazit',
					'link' => ':App:Main:Documents:DocumentAddition:show',
				],
				'edit' => [
					'title' => 'editovat',
					'link' => ':App:Main:Documents:EditDocumentAddition:edit',
				],
				'delete' => [
					'title' => 'smazat',
					'link' => ':App:Main:Documents:DocumentAddition:softDelete',
				],
			],
			'deleted' => false,
			'newRecord' => [
				'title' => 'Nový dodatek',
				'link' => ':App:Main:Documents:AddDocumentAddition:add',
				'args' => ['docId' => $document->getId()],
			],
		]
	);
}
```

Samotné zobrazení datagridu pak obstará tag `control` tedy např.
```
{block content}

<h1>Všechny smlouvy</h1>

{control documentsAll}
```