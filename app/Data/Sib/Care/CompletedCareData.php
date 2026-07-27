<?php

namespace App\Data\Sib\Care;

class CompletedCareData
{
    public function __construct(
        public readonly array $multipleAnswers,
        public readonly array $conditions,
        public readonly array $healthIndexes,
    ) {
    }

    public static function fromApiResponse(array $data): self
    {
        return new self(
            multipleAnswers: array_map(
                fn (array $item) => MultipleAnswer::fromArray($item),
                $data['MultipleAnswers'] ?? []
            ),
            conditions: array_map(
                fn (array $item) => Condition::fromArray($item),
                $data['Conditions'] ?? []
            ),
            healthIndexes: array_map(
                fn (array $item) => HealthIndex::fromArray($item),
                $data['HealthIndexs'] ?? []
            ),
        );
    }

    public function getAnswer($id)
    {
        $answer=[];
        foreach ($this->multipleAnswers??[] as $item){
            if ($item->id==$id){
                $answer[]=$item->idAnswer;
            }
        }
        if (count($answer)){
            return $answer;
        }

        foreach ($this->conditions??[] as $item){
            if ($item->id==$id){
                return $item->idAnswer;
            }
        }
        foreach ($this->healthIndexes??[] as $item){
          if ($item->id==$id){
            return $item->value1;
          }
        }
        return null;
    }
}
