<?php
class HomeController extends BaseController {
    public function index(): void {
        $this->viewFull('home/index');
    }
}