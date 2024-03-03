<?php

declare(strict_types=1);

namespace App\AppModule\AdminModule\MainModule\HomepageModule\Presenters;

use App\AppModule\AdminModule\MainModule\Components\Datagrid\Datagrid;
use App\AppModule\AdminModule\MainModule\Components\Datagrid\DatagridFactory;
use App\AppModule\AdminModule\MainModule\HomepageModule\Forms\Slider\AddFormFactory;
use App\AppModule\AdminModule\MainModule\HomepageModule\Forms\Slider\EditFormFactory;
use App\Libs\Repository\SliderRepository;
use App\Libs\Service\App\CacheService;
use App\Libs\Service\App\SettingsService;
use App\Libs\Utils\Utils;
use Nette\Forms\Form;
use Nette;

class SlidersPresenter extends \App\AppModule\AdminModule\MainModule\BasePresenter
{
	#[Nette\Application\Attributes\Persistent]
	public string $selectedLang;

	protected int $modelId;

	#[Nette\Application\Attributes\Persistent]
	public string $sliderSection;

	public function __construct(
		private DatagridFactory  $datagridFactory,
		private AddFormFactory   $addFormFactory,
		private EditFormFactory  $editFormFactory,
		private SliderRepository $repository,
		private CacheService     $cacheService,
	)
	{
	}

	public function startup()
	{
		parent::startup();

		if (!isset($this->selectedLang)) {
			$this->selectedLang = $this->settingsService->getAppConfig()['langs'][0];
		}
	}

	public function renderDefault(?int $id)
	{
		if ($id) {
			$show = $this->getParameter('show') ?: ($_POST['show'] ?? 'edit');
			$model = $this->repository->getByPk($id);
			$this->getTemplate()->model = $model;
			$this->getTemplate()->show = $show;

			$this->getPayload()->showModal = "#{$show}FormModal";
			$this->redrawControl($show. 'Modal');


			if (isset($_FILES['file_to_upload']['tmp_name']) && $_FILES['file_to_upload']['tmp_name']) {
				$file_name = $_FILES['file_to_upload']['name'] ?? null;
				$file_name = Utils::nice_uri($file_name);
				$file_temp_location = $_FILES['file_to_upload']['tmp_name'] ?? null;
				if (!is_dir("docs/homepage/sliders/{$id}")) {
					mkdir("docs/homepage/sliders/{$id}",0777, true);
				}
				if (!$file_temp_location) {
					$this->flashMessage('ERROR: No file has been selected', 'danger');
				}  elseif (move_uploaded_file($file_temp_location, "docs/homepage/sliders/{$id}/$file_name")){
					$model->set('photo',$file_name)->update();
					$this->getTemplate()->messages = [$this->translator->translate(
						'Photo was successfully uploaded'
					)];
				} else {
					$this->flashMessage('Server Error', 'danger');
				}
			}

		} elseif (isset($this->modelId) || isset($_POST['id'])) {
			$this->getTemplate()->model = $this->repository->getByPk($this->modelId ?? (int)$_POST['id']);
			$this->getTemplate()->show = 'edit';
		}

		$this->getTemplate()->selectedLang = $this->selectedLang;
		$this->getTemplate()->sliderSection = $this->sliderSection;
	}

	public function actionDelete(int $id)
	{
		//if ($this->isAjax()) {
			$model = $this->repository->getByPk($id);
			$model->set('deleted', 1)->update();
			$this->cacheService->removeKey('hpsliders-' . $model->get('lang')->getValue());
			$this->flashMessage('Record was successfully deleted', 'success');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		//}
		$this->redirect('default');
	}

	public function actionMoveUp(int $id)
	{
		//if ($this->isAjax()) {
			$this->repository->moveUpAction($id);
			$this->flashMessage($this->translator->translate(
				'Record has been successfully moved up'
			), 'success');
		//}
		$this->redirect('default');
	}

	public function actionMoveDown(int $id)
	{
		//if ($this->isAjax()) {
			$this->repository->moveDownAction($id);
			$this->flashMessage($this->translator->translate(
				'Record has been successfully moved down'
			), 'success');
		//}
		$this->redirect('default');
	}

	public function createComponentSliders(): Datagrid
	{
		return $this->datagridFactory->create(
			$this->repository,
			[
				'conditions' => [
					[
						'column' => 'section',
						'op' => '=',
						'value' => $this->sliderSection,
					],
					[
						'column' => 'lang',
						'op' => '=',
						'value' => $this->selectedLang,
					],
					[
						'column' => 'deleted',
						'op' => '=',
						'value' => 0,
					],
				],
				'columns' => [
					'photo' => [
						'type' => 'none',
						'sorting' => false,
						'title' => $this->translator->translate('Photo'),
						'render' => function($record, $photo) {
							return "<img class='' style='width:100px;height:100px;border-radius:0;' src='/"
								. ($this->settingsService->getAppConfig()['subdir']
									. ($record->get($photo->getName())->getValue()
										? "docs/homepage/sliders/".$record->get('id')->getValue() ."/" .$record->get($photo->getName())->getValue()
										: "assets/admin/images/empty.jpg")
								)."'>";
						},
					],
					'active' => [
						'title' => $this->translator->translate('Active'),
						'type' => 'radio',
					],
					'title' => [
						'title' => $this->translator->translate('Title'),
					],
					'linkText' => [
						'title' => $this->translator->translate('Link text'),
					],
					'link' => [
						'title' => $this->translator->translate('Link'),
					],
				],
				'actions' => [
					'edit' => [
						'title' => '<span class="mdi mdi-table-edit" title="'.$this->translator->translate('Edit').'"></span>',
						'link' => ':App:Admin:Main:Homepage:Sliders:',
						'args' => ['show' => 'edit'],
						'class' => 'ajax',
					],
					'moveUp' => [
						'title' => '<span class="mdi mdi-arrow-up-drop-circle" title="'.$this->translator->translate('Move Up').'"></span>',
						'link' => ':App:Admin:Main:Homepage:Sliders:moveUp',
						'class' => 'ajax',
					],
					'moveDown' => [
						'title' => '<span class="mdi mdi-arrow-down-drop-circle" title="'.$this->translator->translate('Move Down').'"></span>',
						'link' => ':App:Admin:Main:Homepage:Sliders:moveDown',
						'class' => 'ajax',
					],
					'delete' => [
						'title' => '<span class="mdi mdi-delete" title="'.$this->translator->translate('Delete').'"></span>',
						'link' => ':App:Admin:Main:Homepage:Sliders:delete',
					],
				],
				'newRecord' => [
					'title' => $this->translator->translate('Add new record'),
					'link' => '#add-new-record',
					'args' => [],
				],
				'deleted' => null,
				'defaultSorting' => ['rank','asc'],
			]
		);
	}

	/**
	 * @return Form
	 */
	protected function createComponentEditForm(): Form
	{
		return $this->editFormFactory->create(function (bool $succ): void {
			if ($succ) {
				$this->getTemplate()->messages = [$this->translator->translate(
					'Record has been successfully changed'
				)];
			}
			$this->redrawControl('datagrid');
			$this->redrawControl('datagridWrapper');
			$this->redrawControl('editModal');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		}, ((int)$this->getParameter('id')) ?: (int)($this->modelId ?? $_POST['modelId']));
	}

	/**
	 * @return Form
	 */
	protected function createComponentAddForm(): Form
	{
		return $this->addFormFactory->create($this->sliderSection, $this->selectedLang, function (bool $succ, ?int $id): void {
			if ($succ) {
				$this->getTemplate()->messages = [$this->translator->translate(
					'Record has been successfully created'
				)];
				$this->getPayload()->closeModal = '#addFormModal';
				$this->getPayload()->showModal = '#editFormModal';
				$this->redrawControl('editModal');
				$this->modelId = $id;
			}
			$this->getPayload()->afterForm = true;

			$this->redrawControl('datagrid');
			$this->redrawControl('datagridWrapper');
			$this->redrawControl('addModal');
			$this->redrawControl('flashMessages');
			$this->redrawControl('contentWrapper');
		});
	}
}