<?php



namespace App\Resources\Suggestion {

  use App\Resources\Common\Utilities\GlobalProxy;
  use Exception;
  use RestClientException;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  readonly class SuggestionController {
    private SuggestionService $suggestionService;



    /**
     * @throws Exception
     */
    public function __construct() {
      $this->suggestionService = GlobalProxy::$container->get(SuggestionService::class);
    }



    /**
     * @return SuggestionEntity[]
     * @throws RestClientException
     */
    #[Query]
    #[Logged]
    public function suggestions(string $query): array {
      return $this->suggestionService->suggestions($query);
    }
  }
}
