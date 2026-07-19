<?php

namespace App\Providers;

use App\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use PhpParser\Node\Expr\AssignOp\Mod;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class QueryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->toRawSql();
        $this->getTable();
        $this->getColumns();
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    protected function toRawSql()
    {
        Builder::macro('toRawSql', function () {
            return array_reduce($this->getBindings(), function ($sql, $binding) {
                return preg_replace('/\?/', is_numeric($binding) ? $binding : "'" . $binding . "'", $sql, 1);
            }, $this->toSql());
        });
        EloquentBuilder::macro('toRawSql', function () {
            return ($this->getQuery()->toRawSql());
        });
    }

    protected function getTable()
    {
        EloquentBuilder::macro('table', function () {
            return $this->getModel()->getTable();
        });
    }

    protected function getColumns()
    {
        EloquentBuilder::macro('getColumns', function () {
           return Schema::getColumnListing($this->getModel()->getTable());
        });
    }
}
