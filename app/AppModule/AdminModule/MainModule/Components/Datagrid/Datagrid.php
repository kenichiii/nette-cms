<?php declare(strict_types = 1);

namespace App\AppModule\AdminModule\MainModule\Components\Datagrid;

use App\Libs\Kenichi\ORM\Model;
use App\Libs\Kenichi\ORM\Repository;
use App\Libs\Kenichi\ORM\SelectQuery;
use App\Libs\Service\App\Translator;
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

final class Datagrid extends Control
{
	protected string $templateFile = __DIR__ . '/templates/datagrid.latte';
	private SessionSection $sessionSection;

	protected array $columns = [];
	protected array $filters = [];
	/** @var Model[] */
	protected array $records = [];
	protected array $actions = [];
	protected ?bool $deleted = null;
	protected array $conditions = [];
	protected array $defaultSorting = [];
	protected SelectQuery $select;

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

	public int $actualPage=1;
	public int $pages;
	public int $minPage;
	public int $maxPage;
	public int $limit;
	public int $offset;
	public int $recordsCount=0;

	protected ?array $newRecordButton;

	public function __construct(
		array $definition,
		protected Repository $repository,
		Session $session,
		protected Translator $translator,
	)
	{
		$this->sessionSection = $session->getSection($this->getId());
		$this->buildColumns($definition['columns']);
		$this->actions = $definition['actions'] ?? [];
		$this->newRecordButton = $definition['newRecord'] ?? null;
		$this->deleted = $definition['deleted'] ?? null;
		$this->conditions = $definition['conditions'] ?? [];
		$this->defaultSorting = $definition['defaultSorting'] ?? ['id','desc'];
	}
	public function getId()
	{
		return str_replace('\\','', $this->repository->getModelClassName()).'Datagrid';
	}
	protected function buildColumns(array $columns)
	{
		foreach ($columns as $key => $column) {
			$this->columns []= new DatagridColumn($key, $column);
		}
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
		$this->buildSelect();


		//$result = call_user_func([$this->QMANService, $this->getData], $this->qmanFilters);
		$select = $this->getSelect();
		$this->records = $select->fetchData();
		$this->recordsCount = $select->clearLimit()->getCount();


		$count = $this->recordsCount / $this->limit;
        $this->pages = (int) ceil($count);


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

	protected function buildSelect()
	{


		//prepare query
		$select = $this->repository->getSelect();
		$select->limit($this->limit, $this->offset);

		if ($this->filters['fulltext']) {
			foreach ($this->repository->getModel()->getColumnsNames() as $key => $value) {
				if ($key === array_key_first($this->repository->getModel()->getColumnsNames())) {
					$select->andWhere('('.$key.' like %s','%'.$this->filters['fulltext'].'%');
				}
				$select->orWhere($key.' like %s','%'.$this->filters['fulltext'].'%');
			}
 			$select->where(')');
			/*
			foreach ($this->getColumns() as $column) {
				if ($column->getType() === 'text' || $column->getType() === 'select')  {
					$select->andWhere($column->getName().' like %s','%'.$this->filters[$column->getName()].'%');
				}
			}*/
			//$query ['fulltext'] = $this->filters['fulltext'];
		}

		foreach ($this->conditions as $condition) {
			$select->andWhere(
				$condition['column'].$condition['op'].$this->repository->getModel()[$condition['column']]->getDibiModificator(),
				$condition['value']
			);
		}

		if ($this->deleted !== null) {
			$select->andWhere('deleted = %i', 1);
		}

		if ($this->filters['sortColumn']) {
			$select->orderBy($this->filters['sortColumn'],strtolower($this->filters['sortSort']));
		} else {
			$select->orderBy($this->defaultSorting[0],$this->defaultSorting[1]);
		}

		foreach ($this->getColumns() as $column) {
			switch ($column->getType()) {
				case 'date':
					if ($this->filters[$column->getName().'_from'] || $this->filters[$column->getName().'_to']) {
						if ($this->filters[$column->getName().'_from']) {
							$select->andWhere($column->getName().' >  %s', $this->filters[$column->getName().'_from']);
						}

						if ($this->filters[$column->getName().'_to']) {
							$select->andWhere($column->getName().' < %s ', $this->filters[$column->getName().'_to']);
						}
					}
					break;
				case 'datetime':
					if ($this->filters[$column->getName().'_from'] || $this->filters[$column->getName().'_to']) {
						if ($this->filters[$column->getName().'_from']) {
							$select->andWhere($column->getName().' >  %s', date('Y-m-d H:i:s', strtotime($this->filters[$column->getName().'_from'])));
						}

						if ($this->filters[$column->getName().'_to']) {
							$select->andWhere($column->getName().' < %s ', date('Y-m-d H:i:s', strtotime($this->filters[$column->getName().'_to'])));
						}
					}
					break;

				case 'select':
					if ($this->filters[$column->getName()] && intval($this->filters[$column->getName()])) {
						$select->andWhere($column->getName().'=%s', $this->filters[$column->getName()]);
					}
					break;
				case 'radio':
					if ($this->filters[$column->getName()] && $this->filters[$column->getName()] !== 'all') {
						$select->andWhere($column->getName().'=%i', $this->filters[$column->getName()] === 'yes' ? 1 : 0 );
					}
					break;
				case 'text':
					if ($this->filters[$column->getName()]) {
						$select->andWhere($column->getName().' like %s','%'.$this->filters[$column->getName()].'%');
					}
					break;
				default:
					break;
			}
		}

		$this->select = $select;
	}

	public function getSelect(): SelectQuery
	{
		return $this->select;
	}

	/**
	 * @return Model[]
	 */
	public function getRecords(): array
	{
		return $this->records;
	}

	public function renderFilter($column): string
	{
		switch ($column->getType()) {
			case 'date':
				return "<span class='datagrid-filter-date form-group'>
							<input type='date' name='{$column->getName()}_from' class='searchDate form-control "
								.($this->filters["{$column->getName()}_from"] ? 'filter-on' : '')
								."' value='{$this->filters["{$column->getName()}_from"]}'>
							<span class='datagrid-filter-reset-date fa-xl fa-solid fa-close' ".(!$this->filters["{$column->getName()}_from"] ? "style='display:none'" : '')."></span>
						</span>"
					."
						<span class='datagrid-filter-date form-group'>
							<input type='date' name='{$column->getName()}_to' class='searchDate form-control "
								.($this->filters["{$column->getName()}_to"] ? 'filter-on' : '')
								."' value='{$this->filters["{$column->getName()}_to"]}'>
							<span class='datagrid-filter-reset-date fa-xl fa-solid fa-close' ".(!$this->filters["{$column->getName()}_to"] ? "style='display:none'" : '')."></span>
						</span>					
					";
				break;
			case 'datetime':
				return "<span class='datagrid-filter-date form-group'>
							<input type='datetime-local' name='{$column->getName()}_from' class='searchDate form-control "
					.($this->filters["{$column->getName()}_from"] ? 'filter-on' : '')
					."' value='{$this->filters["{$column->getName()}_from"]}'>
							<span class='datagrid-filter-reset-date fa-xl fa-solid fa-close' ".(!$this->filters["{$column->getName()}_from"] ? "style='display:none'" : '')."></span>
						</span>"
					."
						<span class='datagrid-filter-date form-group'>
							<input type='datetime-local' name='{$column->getName()}_to' class='searchDate form-control "
								.($this->filters["{$column->getName()}_to"] ? 'filter-on' : '')
					."' value='{$this->filters["{$column->getName()}_to"]}'>
							<span class='datagrid-filter-reset-date fa-xl fa-solid fa-close' ".(!$this->filters["{$column->getName()}_to"] ? "style='display:none'" : '')."></span>
						</span>					
					";
				break;
			case 'radio':

				return "<duv class='d-flex'><div class='form-check'> 
 						<label  class='form-check-label' for='{$this->getId()}-{$column->getName()}-all'>"
					.	"<input class='searchRadio form-check-input' type='radio' name='{$column->getName()}' value='all' id='{$this->getId()}-{$column->getName()}-all'"
					.('all' == $this->filters[$column->getName()] || !$this->filters[$column->getName()] ? 'checked' : '').">"
					. "{$this->translator->translate('all')}</label></div>"
					."<div class='form-check'> 
						 	<label class='form-check-label' for='{$this->getId()}-{$column->getName()}-yes'>
						 		<input class='searchRadio form-check-input' type='radio' name='{$column->getName()}' value='yes' 
						 			id='{$this->getId()}-{$column->getName()}-yes'"
					. ('yes' === $this->filters[$column->getName()] ? 'checked' : '').">"
					." 	{$this->translator->translate('yes')}
						 			</label></div>"
					."<div class='form-check'> <label class='form-check-label' for='{$this->getId()}-{$column->getName()}-no'>
						 	<input class='searchRadio form-check-input' type='radio' name='{$column->getName()}' value='no' 
						 	 		id='{$this->getId()}-{$column->getName()}-no'"
					.  ('no' == $this->filters[$column->getName()] ? 'checked' : '').">"
					." 	{$this->translator->translate('no')}
						 	 		</label>
						 </div>
						 </div>";
				break;
			case 'nrelationSelect':
			case 'relationSelect':
			case 'select':
				$html = "<div class='form-group'><select name='{$column->getName()}' class='searchSelect form-control 
                         	".($this->filters[$column->getName()] ? 'filter-on' : '')."
                         '>";
				foreach ($column->getOptions() as $key => $title) {
					$html .= "<option value='{$key}' ".($key == $this->filters[$column->getName()] ? 'selected' : '').">{$title}</option>>";
				}
				$html .= '</select></div>';
				return $html;
				break;
			case 'text':
				return "<span class='datagrid-filter form-group'>
							<input type='text' class='search form-control ".
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

	public function renderRecordCell(DatagridColumn $column, $record): string
	{
		if (is_callable($column->renderCell())) {
			return $column->renderCell()($record, $column);
		}
		switch ($column->getType())	{
			case 'date':
				return $record->{$column->getName()}
					? date('d.m.y', strtotime($record->get($column->getName())->getValue()))
					: '-';
				break;
			case 'datetime':
				return $record->{$column->getName()}
					? date('d.m.y G:i', strtotime($record->get($column->getName())->getValue()))
					: '-';
				break;
			case 'radio':
				return $record->get($column->getName())->getValue() === 1
					? '<span class="btn-primary p-2">'.$this->translator->translate('Yes').'</span>'
					: '<span class="btn-secondary p-2">'.$this->translator->translate('No').'</span>';
				break;
			case 'select':
				return htmlspecialchars($column->getOptions()[$record->get($column->getName())->getValue()] ?? '');
				break;

			case 'text':
			default:
				return htmlspecialchars((string) $record->get($column->getName())->getValue());
				break;
		}
	}

	public function render(): void
	{

		if (!$this->getPresenter()->isAjax() || !$this->getParameter('fulltext')) {
			$this->loadData($this->sessionSection->get('data') ?? []);
		}
		$this->getTemplate()->setTranslator($this->translator);
		$this->getTemplate()->setFile($this->templateFile);

		$this->getTemplate()->grid = $this;

		$this->getTemplate()->fulltext = $this->filters['fulltext'] ?? '';

		$this->getTemplate()->sortingColumn = $this->filters ['sortColumn'] ?? '';
		$this->getTemplate()->sortingSort = $this->filters ['sortSort']  ?? '';

		$this->getTemplate()->render();
	}

	public function createComponentFilterForm(): Form
	{
		$form = new Form();

		$form->addSubmit('send');

        $form->onSuccess[] = function(Form $form, \stdClass $data) {

			$data = $form->getHttpData();

			$this->loadData($data);
			bdump($data);
			$this->getPresenter()->payload->datagrid = $this->getId();
			//$this->getPresenter()->redrawControl($this->getId());
			$this->getPresenter()->redrawControl('datagrid');
			$this->getPresenter()->redrawControl('datagridWrapper');
			$this->getPresenter()->redrawControl('contentWrapper');
			$this->redirect('this');
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
}