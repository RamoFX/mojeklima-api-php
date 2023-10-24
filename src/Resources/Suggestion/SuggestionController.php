<?php



namespace App\Resources\Suggestion {

  use RestClientException;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  readonly class SuggestionController {
    public function __construct(
      protected SuggestionService $suggestionService
    ) {}



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
