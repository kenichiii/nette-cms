<?php declare(strict_types = 1);

namespace App\Libs\Components\QMANDatagrid;

use App\Libs\Exception\Service\DatagridServiceException;
use App\Libs\Exception\User\CurlClientException;
use App\Libs\Model\Service\QMANService;
use Nette;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Application\UI\Template;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Tracy\Debugger;

/**
 * @property-read Template $template
 */

final class QMANDatagrid extends Control
{
	protected string $templateFile = __DIR__ . '/templates/datagrid.latte';
	private SessionSection $sessionSection;

	protected array $columns;
	protected array $filters;
	protected array $qmanFilters;
	protected array $records = [];
	protected array $actions;
	protected ?bool $deleted;
	protected array $conditions;


	/** @var array<int, string> */
	public array $perPageList = [
		1 => '1',
		10 => '10',
		20 => '20',
		30 => '30',
		40 => '40',
		50 => '50',
		100 => '100',
	];
	private int $defaultPerPage = 10;

	public int $actualPage;
	public int $pages;
	public int $minPage;
	public int $maxPage;
	public int $limit;
	public int $offset;
	public int $recordsCount;

	protected ?array $newRecordButton;

	public function __construct(
		protected string $id,
		protected string $qmanPrefix,
		protected string $getData,
		array $definition,

		Session $session,
		protected QMANService $QMANService,
	)
	{
		$this->sessionSection = $session->getSection($id);
		$this->buildColumns($definition['columns']);
		$this->actions = $definition['actions'] ?? [];
		$this->newRecordButton = $definition['newRecord'] ?? null;
		$this->deleted = $definition['deleted'] ?? null;
		$this->conditions = $definition['conditions'] ?? [];
	}

	protected function buildColumns(array $columns)
	{
		foreach ($columns as $key => $column) {
			$this->columns []= new QMANDatagridColumn($key, $column);
		}
	}


	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	public function getColumns(): array
	{
		return $this->columns;
	}

	public function getActions(): array
	{
		foreach ($this->actions as $key => $action) {
			$this->actions[$key]['args'] = $action['args'] ?? [];
		}
		return $this->actions;
	}

	public function getPerPageOptions(): array
	{
		return $this->perPageList;
	}

	public function loadData(array $data = [])
	{
       	if (isset($data['reset']) && $data['reset'] === 'reset') {
			   $temp = $data['perPage'];
			   $data = [];
			   $data['perPage'] = $temp;
		}

		$this->actualPage = (int) ($data['page'] ?? 1);

		$this->limit  = (int) ($data['perPage'] ?? $this->defaultPerPage);
		$this->offset = ($this->actualPage - 1) * $this->limit;


		$this->buildUIFilters($data);
		$this->buildQMANFilters();


		//$result = call_user_func([$this->QMANService, $this->getData], $this->qmanFilters);
		$result = $this->getData();
		$this->records = $result['data'];
		$this->recordsCount = $result['meta']['count'];


		$count = $this->recordsCount / $this->limit;
        $this->pages = (int) round($count);


        // rozmezi -10 od page a +10 od page
        $this->minPage = $this->actualPage - 5;
        $this->maxPage = $this->actualPage + 5;
        if ($this->minPage < 1) {
			$this->minPage = 1;
		}
        if ($this->maxPage > round($count)) {
			$this->maxPage = (int) round($count);
		}

		$this->sessionSection->set('data', $data);
	}

	protected function buildUIFilters(array $data)
	{
		$this->filters = [];
		$this->filters ['fulltext'] = $data['fulltext'] ?? '';
		$this->filters ['sortColumn'] = $data['sortColumn'] ?? '';
		$this->filters ['sortSort'] = $data['sortSort'] ?? '';

		foreach ($this->getColumns() as $column) {
			if ($column->getType() === 'datetime' || $column->getType() === 'date') {
				$this->filters["{$column->getName()}_from"] = $data["{$column->getName()}_from"] ?? null;
				$this->filters["{$column->getName()}_to"] = $data["{$column->getName()}_to"] ?? null;
			} else {
				$this->filters [$column->getName()] = $data[$column->getName()] ?? null;
			}
		}
	}

	protected function buildQMANFilters()
	{
		$this->qmanFilters = [];

		//prepare query
		$query = [
			'registerId' => (int) $this->QMANService->getAppHashes()->{"{$this->qmanPrefix}Registerid"},
			'limit' => $this->limit,
			'offset' => $this->offset,
		];

		if ($this->filters['fulltext']) {
			$query ['fulltext'] = $this->filters['fulltext'];
		}

		$this->qmanFilters['query'] = $query;

		//create filterEments array from conditions or empty array
		$filterElements = [];
		foreach ($this->conditions as $condition) {
			$condColumn = ucfirst($condition['column']);
			$filterElements [] = [
				'type' => 'rd',
				'el' => (string) $this->QMANService->getIdFromProp(
					$this->QMANService->getAppHashes()->{"{$this->qmanPrefix}{$condColumn}"}
				),
				'op' => $condition['op'],
				'val' => $condition['val'],
			];
		}

		if ($this->deleted !== null) {
			$filterElements []= [
				'type' => 'rd',
				'el' => (string) $this->QMANService->getIdFromProp(
					$this->QMANService->getAppHashes()->{"{$this->qmanPrefix}Deleted"}
				),
				'op' => $this->deleted ? 'equal' : 'notequal',
				'val' => 1,
			];
		}

		if ($this->filters['sortColumn']) {
			$sortColumn = $this->qmanPrefix . ucfirst($this->filters['sortColumn']);
			$this->qmanFilters['order'] = [
				[
					'type' => 'rd',
					'id' => $this->QMANService->getIdFromProp(
						$this->QMANService->getAppHashes()->{$sortColumn}
					),
					'dir' => strtolower($this->filters['sortSort'])
				]
			];
		}

		foreach ($this->getColumns() as $column) {
			switch ($column->getType()) {
				case 'date':
					if ($this->filters[$column->getName().'_from'] || $this->filters[$column->getName().'_to']) {
						$filterElements [] = [
							'type' => 'rd',
							'el' => (string) $this->QMANService->getIdFromProp(
								$this->QMANService->getAppHashes()->{"{$this->qmanPrefix}{$column->getNameCapitalized()}"}
							),
							'op' => 'range',
							'val' => [
								$this->filters[$column->getName().'_from'] ?: null,
								$this->filters[$column->getName().'_to'] ?: null
							]
						];
					}
					break;
				case 'datetime':
					if ($this->filters[$column->getName().'_from'] || $this->filters[$column->getName().'_to']) {
						$filterElements [] = [
							'type' => 'rd',
							'el' => (string) $this->QMANService->getIdFromProp(
								$this->QMANService->getAppHashes()->{"{$this->qmanPrefix}{$column->getNameCapitalized()}"}
							),
							'op' => 'range',
							'val' => [
								$this->filters[$column->getName().'_from'] ?  date('Y-m-d H:i:s', strtotime($this->filters[$column->getName().'_from'])) : null,
								$this->filters[$column->getName().'_to'] ?  date('Y-m-d H:i:s', strtotime($this->filters[$column->getName().'_to'])) : null
								]
						];
					}
					break;
				case 'nrealtionSelect':
				case 'relationSelect':
					if ($this->filters[$column->getName()] && intval($this->filters[$column->getName()])) {
						$filterElements []= [
							'type' => 'rd',
							'el' => (string) $this->QMANService->getIdFromProp(
								$this->QMANService->getAppHashes()->{"{$this->qmanPrefix}{$column->getNameCapitalized()}"}
							),
							'op' => 'include',
							'val' => [intval($this->filters[$column->getName()])],
						];
					}
					break;
				case 'radio':
					if ($this->filters[$column->getName()] && $this->filters[$column->getName()] !== 'all') {
						$filterElements []= [
							'type' => 'rd',
							'el' => (string) $this->QMANService->getIdFromProp(
								$this->QMANService->getAppHashes()->{"{$this->qmanPrefix}{$column->getNameCapitalized()}"}
							),
							'op' => $this->filters[$column->getName()] === 'yes' ? 'equal' : 'notequal',
							'val' => 1,
						];
					}
					break;
				case 'text':
					if ($this->filters[$column->getName()]) {
						$filterElements [] = [
							'type' => 'rd',
							'el' => (string) $this->QMANService->getIdFromProp(
								$this->QMANService->getAppHashes()->{"{$this->qmanPrefix}{$column->getNameCapitalized()}"}
							),
							'op' => 'include',
							'val' => $this->filters[$column->getName()],
						];
					}
					break;
				default:
					break;
			}
		}

		$this->qmanFilters['filterElements'] = $filterElements;
	}

	public function getRecords(): array
	{
		return $this->records;
	}

	public function renderFilter($column): string
	{
		switch ($column->getType()) {
			case 'date':
				return "<span class='datagrid-filter-date'>
							<input type='date' name='{$column->getName()}_from' class='searchDate form-control-sm "
								.($this->filters["{$column->getName()}_from"] ? 'filter-on' : '')
								."' value='{$this->filters["{$column->getName()}_from"]}'>
							<span class='datagrid-filter-reset-date fa-xl fa-solid fa-close' ".(!$this->filters["{$column->getName()}_from"] ? "style='display:none'" : '')."></span>
						</span>"
					."
						<span class='datagrid-filter-date'>
							<input type='date' name='{$column->getName()}_to' class='searchDate form-control-sm "
								.($this->filters["{$column->getName()}_to"] ? 'filter-on' : '')
								."' value='{$this->filters["{$column->getName()}_to"]}'>
							<span class='datagrid-filter-reset-date fa-xl fa-solid fa-close' ".(!$this->filters["{$column->getName()}_to"] ? "style='display:none'" : '')."></span>
						</span>					
					";
				break;
			case 'datetime':
				return "<span class='datagrid-filter-date'>
							<input type='datetime-local' name='{$column->getName()}_from' class='searchDate form-control-sm "
					.($this->filters["{$column->getName()}_from"] ? 'filter-on' : '')
					."' value='{$this->filters["{$column->getName()}_from"]}'>
							<span class='datagrid-filter-reset-date fa-xl fa-solid fa-close' ".(!$this->filters["{$column->getName()}_from"] ? "style='display:none'" : '')."></span>
						</span>"
					."
						<span class='datagrid-filter-date'>
							<input type='datetime-local' name='{$column->getName()}_to' class='searchDate form-control-sm "
								.($this->filters["{$column->getName()}_to"] ? 'filter-on' : '')
					."' value='{$this->filters["{$column->getName()}_to"]}'>
							<span class='datagrid-filter-reset-date fa-xl fa-solid fa-close' ".(!$this->filters["{$column->getName()}_to"] ? "style='display:none'" : '')."></span>
						</span>					
					";
				break;
			case 'radio':
				return "<input class='searchRadio' type='radio' name='{$column->getName()}' value='all' id='{$this->getId()}-{$column->getName()}-all'"
							.('all' == $this->filters[$column->getName()] || !$this->filters[$column->getName()] ? 'checked' : '').">"
						." <label for='{$this->getId()}-{$column->getName()}-all'>vše</label>"
						."<span class='".('yes' == $this->filters[$column->getName()] ? 'filter-on' : '')."'>
						 	<input class='searchRadio' type='radio' name='{$column->getName()}' value='yes' 
						 			id='{$this->getId()}-{$column->getName()}-yes' 
						 			{('yes' == $this->filters[$column->getName()] ? 'checked' : '')}>"
						." 	<label for='{$this->getId()}-{$column->getName()}-yes'>ano</label>
						 </span>"
						."<span class='".('no' == $this->filters[$column->getName()] ? 'filter-on' : '')."'>
						 	<input class='searchRadio' type='radio' name='{$column->getName()}' value='no' 
						 	 		id='{$this->getId()}-{$column->getName()}-no'"
						.			('no' == $this->filters[$column->getName()] ? 'checked' : '').">"
						." 	<label for='{$this->getId()}-{$column->getName()}-no'>ne</label>
						 </span>";
				break;
			case 'nrelationSelect':
			case 'relationSelect':
			case 'select':
				$html = "<select name='{$column->getName()}' class='searchSelect form-control-sm 
                         	".($this->filters[$column->getName()] ? 'filter-on' : '')."
                         '>";
				foreach ($column->getOptions() as $key => $title) {
					$html .= "<option value='{$key}' ".($key == $this->filters[$column->getName()] ? 'selected' : '').">{$title}</option>>";
				}
				$html .= '</select>';
				return $html;
				break;
			case 'text':
				return "<span class='datagrid-filter'>
							<input type='text' class='search form-control-sm ".
								($this->filters["{$column->getName()}"] ? 'filter-on' : '')."' 
								name='{$column->getName()}' 
								value='{$this->filters[$column->getName()]}'>
							<span class='datagrid-filter-reset fa-xl fa-close fa-solid' ".(!$this->filters["{$column->getName()}"] ? "style='display:none'" : '')."></span>	
						</span>		
						";
				break;
			default:
				return '';
		}
	}

	public function renderRecordCell(QMANDatagridColumn $column, $record): string
	{
		if (is_callable($column->renderCell())) {
			return $column->renderCell()($record, $column);
		}
		switch ($column->getType())	{
			case 'date':
				return $record->{$column->getName()}
					? date('d.m.y', strtotime($record->{$column->getName()}))
					: '-';
				break;
			case 'datetime':
				return $record->{$column->getName()}
					? date('d.m.y G:i', strtotime($record->{$column->getName()}))
					: '-';
				break;
			case 'radio':
				return $record->{$column->getName()} === 'yes'
					? '<span class="bg-primary">Ano</span>'
					: '<span class="bg-secondary">Ne</span>';
				break;
			case 'nrelationSelect':
				$html = '<ul>';
				foreach ($record->{$column->getName()} as $value) {
					$html .= '<li>' . htmlspecialchars($column->getOptions()[$value] ?? '') . '</li>';
				}
				$html .= '</ul>';
				return $html;
				break;
			case 'relationSelect':
			case 'select':
				return htmlspecialchars($column->getOptions()[$record->{$column->getName()}] ?? '');
				break;

			case 'text':
			default:
				return htmlspecialchars((string) $record->{$column->getName()});
				break;
		}
	}

	public function render(): void
	{
		if (!$this->getPresenter()->isAjax()) {
			$this->loadData($this->sessionSection->get('data') ?? []);
		}

		$this->getTemplate()->setFile($this->templateFile);

		$this->getTemplate()->grid = $this;

		$this->getTemplate()->fulltext = $this->filters['fulltext'] ?? '';

		$this->getTemplate()->sortingColumn = $this->filters ['sortColumn'] ?? '';
		$this->getTemplate()->sortingSort = $this->filters ['sortSort']  ?? '';

		$this->getTemplate()->snippetArea = "datagrid".$this->getId();
		$this->getTemplate()->snippet = "records".$this->getId();

		$this->getTemplate()->render();
	}

	public function createComponentFilterForm(): Form
	{
		$form = new Form();

		$form->addSubmit('send');

        $form->onSuccess[] = function(Form $form, \stdClass $data) {

			$data = $form->getHttpData();

			$this->loadData($data);
			$this->getPresenter()->payload->datagrid = $this->getId();
			$this->redrawControl();
		};

		return $form;
	}

	public function isNewRecordButton(): bool
	{
		return $this->newRecordButton !== null;
	}

	public function getNewRecordButton(): ?array
	{
		$this->newRecordButton['args'] = $this->newRecordButton['args'] ?? [];
		return $this->newRecordButton;
	}

	public static function sanitize_string(string $string): string
	{
		$friendlyurl = preg_replace('/!([a-zA-Z0-9])/','-',$string);
		$tbl = array("\xc3\xa1"=>"a","\xc3\xa4"=>"a","\xc4\x8d"=>"c","\xc4\x8f"=>"d","\xc3\xa9"=>"e","\xc4\x9b"=>"e","é"=>"e","\xc3\xad"=>"i","\xc4\xbe"=>"l","\xc4\xba"=>"l","\xc5\x88"=>"n","\xc3\xb3"=>"o","\xc3\xb6"=>"o","\xc5\x91"=>"o","\xc3\xb4"=>"o","\xc5\x99"=>"r","\xc5\x95"=>"r","\xc5\xa1"=>"s","\xc5\xa5"=>"t","\xc3\xba"=>"u","\xc5\xaf"=>"u","\xc3\xbc"=>"u","\xc5\xb1"=>"u","\xc3\xbd"=>"y","\xc5\xbe"=>"z","\xc3\x81"=>"A","\xc3\x84"=>"A","\xc4\x8c"=>"C","\xc4\x8e"=>"D","\xc3\x89"=>"E","\xc4\x9a"=>"E","\xc3\x8d"=>"I","\xc4\xbd"=>"L","\xc4\xb9"=>"L","\xc5\x87"=>"N","\xc3\x93"=>"O","\xc3\x96"=>"O","\xc5\x90"=>"O","\xc3\x94"=>"O","\xc5\x98"=>"R","\xc5\x94"=>"R","\xc5\xa0"=>"S","\xc5\xa4"=>"T","\xc3\x9a"=>"U","\xc5\xae"=>"U","\xc3\x9c"=>"U","\xc5\xb0"=>"U","\xc3\x9d"=>"Y","\xc5\xbd"=>"Z");
		return urlencode(str_replace(' ','-',strtolower(strtr($friendlyurl, $tbl))));
	}

	protected function getData()
	{
		$filters = $this->qmanFilters;
		$serializedFilters = Nette\Utils\Json::encode($filters);

		$key = $this->getId()."-{$this->QMANService->getAppCompany()->getPointer()}-{$serializedFilters}";

		return $this->QMANService->getCache()->load($key, function () use ($key, $filters) {
			try {
				$json = [
					'registerdetailFilterElements' => $filters['filterElements'] ?? [],
				];

				if (isset($filters['order'])) {
					$json ['order']= $filters['order'];
				}

				$response = $this->QMANService->post(
					"workspace/{$this->QMANService->getAppCompany()->getWorkspace()}/registeritem/search",
					[
						'query' => $filters['query'],
						'json' => $json
					]
				);
				$response = Nette\Utils\Json::decode((string)$response);

				if ($response->status === 'error') {
					Debugger::log($response);
					throw new DatagridServiceException($response->message);
				}
			} catch (CurlClientException|Nette\Utils\JsonException $e) {
				Debugger::log($e);
				throw new DatagridServiceException($e->getMessage());
			}

			$records = [];

			foreach ($response->data->registeritems as $id => $value) {
				$rec = $response->data->datasetResult->values->{$value->id};
				$record = new \stdClass();
				$record->id = $value->id;
				$record->uniqueHash = $value->uniquehash;

				foreach ($this->getColumns() as $column)
				{
					$hash = $this->QMANService->getAppHashes()->{$this->qmanPrefix.$column->getNameCapitalized()};
					$record->{$column->getName()} = match ($column->getType()) {
						'nrelationSelect' => $this->QMANService->getIdsFromRelation($rec->{$hash}),
						'relationSelect' => $this->QMANService->getIdFromRelation($rec->{$hash}),
						default => $rec->{$hash},
					};
				}
				$records [] = $record;
			}

			return ['meta' => ['count' => $response->data->totalCount], 'data' => $records];
		}, [
			Nette\Caching\Cache::Tags => [$this->QMANService->getAppCompany()->getPointer()]
		]);
	}
}