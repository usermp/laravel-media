<?php

namespace Usermp\LaravelMedia\Services;

use Usermp\LaravelMedia\Services\Constants;
use Usermp\LaravelMedia\Services\Response;
use Illuminate\Http\JsonResponse;

class Crud
{
    public static function index($model, $customSetting = null): JsonResponse
    {
        $resource = func_num_args() > 1 ? $customSetting : $model::all();
        return Response::success(Constants::SUCCESS,$resource);
    }

    public static function store(array $fields, $model): JsonResponse
    {
        try {
            $response = $model::create($fields);
            return Response::success(Constants::SUCCESS, $response,201);
        } catch (\Exception $exception) {
            \Sentry\captureException($exception);
            return Response::error(env("APP_DEBUG") ? $exception->getMessage() : Constants::ERROR_STORE);
        }
    }

    public static function show($model, $customSetting = null): JsonResponse
    {
        try {
            $resource = func_num_args() > 1 ? $customSetting : $model;
            return Response::success(Constants::SUCCESS, $resource);
        } catch (\Exception $exception) {
            \Sentry\captureException($exception);
            return Response::error(env("APP_DEBUG") ? $exception->getMessage() : Constants::ERROR);
        }
    }

    public static function update(array $fields, $model): JsonResponse
    {
        try {
            $model->update($fields);
            return Response::success(Constants::SUCCESS_UPDATE, $model);
        } catch (\Exception $exception) {
            \Sentry\captureException($exception);
            return Response::error(env("APP_DEBUG") ? $exception->getMessage() : Constants::ERROR_UPDATE);
        }
    }

    public static function destroy($model): JsonResponse
    {
        try {
            $model->delete();
            return Response::success(Constants::SUCCESS_DELETE);
        } catch (\Exception $exception) {
            \Sentry\captureException($exception);
            return Response::error(env("APP_DEBUG") ? $exception->getMessage() : Constants::ERROR_DELETE);
        }
    }
}
