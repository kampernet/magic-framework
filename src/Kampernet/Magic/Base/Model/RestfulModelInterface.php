<?php
namespace Kampernet\Magic\Base\Model;

/**
 * Interface RestfulModelInterface
 *
 * A wrapper interface to identify model classes you want to automap RESTful requests to.
 * POST /model ( populates the Model properties with the request body, then calls Model::save )
 * PUT /model ( populates the Model properties with the request body, then calls Model::save )
 * DELETE /model ( populates the Model properties with the query string, then calls Model::delete )
 * HEAD /model ( calls Model::head if exists, otherwise throws NoSuchMethodException )
 * TRACE /model ( calls Model::trace if exists, otherwise throws NoSuchMethodException )
 * CONNECT /model ( calls Model::connect if exists, otherwise throws NoSuchMethodException )
 * GET /model ( populates the Model properties with the query string, then calls Model::fetch )
 * OPTIONS /model ( calls Model::options if exists, otherwise throws NoSuchMethodException )
 */
interface RestfulModelInterface {

}