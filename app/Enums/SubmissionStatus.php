<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Draft = 'draft';
    case AbstractSubmitted = 'abstract_submitted';
    case Screening = 'screening';
    case UnderReview = 'under_review';
    case RevisionRequired = 'revision_required';
    case RevisionSubmitted = 'revision_submitted';
    case AbstractAccepted = 'abstract_accepted';
    case AbstractRejected = 'abstract_rejected';
    case LoaIssued = 'loa_issued';
    case FullPaperSubmitted = 'full_paper_submitted';
    case FullPaperReview = 'full_paper_review';
    case FullPaperRevisionRequired = 'full_paper_revision_required';
    case FullPaperAccepted = 'full_paper_accepted';
    case Scheduled = 'scheduled';
    case Presented = 'presented';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::AbstractSubmitted => 'Abstract Submitted',
            self::Screening => 'Screening',
            self::UnderReview => 'Under Review',
            self::RevisionRequired => 'Revision Required',
            self::RevisionSubmitted => 'Revision Submitted',
            self::AbstractAccepted => 'Abstract Accepted',
            self::AbstractRejected => 'Abstract Rejected',
            self::LoaIssued => 'LoA Issued',
            self::FullPaperSubmitted => 'Full Paper Submitted',
            self::FullPaperReview => 'Full Paper Review',
            self::FullPaperRevisionRequired => 'Full Paper Revision Required',
            self::FullPaperAccepted => 'Full Paper Accepted',
            self::Scheduled => 'Scheduled',
            self::Presented => 'Presented',
            self::Completed => 'Completed',
        };
    }
}
